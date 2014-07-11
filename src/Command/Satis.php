<?php
namespace Bolt\Extensions\Command;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputOption;

use Doctrine\ORM\EntityManager;
use Bolt\Extensions\Entity;



class Satis extends Command {

    public $em;
    
 
    public function __construct(EntityManager $em = null) {
        if(false !== $em) $this->em = $em;
        parent::__construct();
    }


    protected function configure() {
        $this->setName("bolt:satis")
                ->setDescription("Compiles a satis.json file from all registered packages");
    }

    protected function execute(InputInterface $input, OutputInterface $output) {
        
        $repo = $this->em->getRepository(Entity\Package::class);
        $packages = $repo->findBy(['approved'=>true]);
        $repo = [
            'name'=> 'Bolt Extensions Repository',
            'homepage' => 'http://bolt.rossriley.co.uk/satis',
            'repositories' => [],
            'output-dir' => getcwd().'/public/satis/'
        ];
        foreach($packages as $package) {
            $repo['repositories'][] = ['type'=>'vcs', 'url'=> $package->source];
        }
        $satis = json_encode($repo);
        $file = getcwd()."/satis.json";
        $result = file_put_contents($file, $satis);
        if($result) {
            $output->writeln("<info>Satis configuration written to $file</info>");
        } else {
            $output->writeln("<error>Could not write Satis configuration to $file check file or directory permissions.</error>");
        }
    }
    


}