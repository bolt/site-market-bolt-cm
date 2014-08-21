<?php
namespace Bolt\Extensions\Command;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputOption;

use Doctrine\ORM\EntityManager;
use Bolt\Extensions\Entity;

use Bolt\Extensions\Service\PackageManager;


class UpdatePackage extends Command {

    public $em;
    
 
    public function __construct(EntityManager $em = null, PackageManager $packageManager = null) {
        if (null !== $em) {
            $this->em = $em;
        }
        if (null !== $packageManager) {
            $this->packageManager = $packageManager;
        }
        parent::__construct();
    }


    protected function configure() {
        $this->setName("bolt:update")
                ->setDescription("Updates the registered extensions on a random basis")
                ->addArgument('name', InputArgument::OPTIONAL, 'If set, this package will update');
    }

    protected function execute(InputInterface $input, OutputInterface $output) {
        $repo = $this->em->getRepository(Entity\Package::class);
        if ($input->getArgument('name')) {
            $package = $repo->findOneBy(['name'=>$input->getArgument('name')]);
        } else {
           $packages = $repo->findBy(['approved'=>true]); 
           $package = $packages[array_rand($packages)];
        }
        
        
        $output->writeln("<info>Updating ".$package->getName()."</info>");
        try {
            $package = $this->packageManager->syncPackage($package);
            if ($package->account->approved == true) {
               $package->approved = true; 
            }  
        } catch (\Exception $e) {
            $output->writeln("<error>".$e->getMessage()."</error>");
            $package->approved = false;
        }
        
        $this->em->persist($package);
        $this->em->flush();
        $output->writeln("<comment>Update Complete</comment>");
            
    }
    


}