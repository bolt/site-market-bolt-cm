<?php
namespace Bolt\Extensions\Command;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputOption;

use Doctrine\ORM\EntityManager;
use Bolt\Extensions\Entity;



class UpdatePackage extends Command {

    public $em;
    
 
    public function __construct(EntityManager $em = null) {
        if(false !== $em) $this->em = $em;
        parent::__construct();
    }


    protected function configure() {
        $this->setName("bolt:update")
                ->setDescription("Updates the registered extensions on a random basis");
    }

    protected function execute(InputInterface $input, OutputInterface $output) {
        
        $repo = $this->em->getRepository(Entity\Package::class);
        $packages = $repo->findBy(['approved'=>true]);
        $package = $packages[array_rand($packages)];
        $output->writeln("<info>Updating ".$package->getName()."</info>");
        $package->sync();
        $this->em->persist($package);
        $this->em->flush();
        $output->writeln("<comment>Update Complete</comment>");
            
    }
    


}