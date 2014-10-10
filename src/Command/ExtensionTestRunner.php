<?php
namespace Bolt\Extensions\Command;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Input\ArrayInput;
use Doctrine\ORM\EntityManager;
use Bolt\Extensions\Entity;
use Symfony\Component\Process\Process;




class ExtensionTestRunner extends Command {
    

    public $em;
    public $isRunning = false;
    public $waitTime = 20;
    public $domain = "http://bolt.rossriley.co.uk";
    
 
    public function __construct(EntityManager $em) {
        $this->em = $em;
        parent::__construct();
    }
    

    protected function configure() {
        $this->setName("bolt:extension-tester")
                ->setDescription("Looks in the queue and launches a test instance of a Bolt with extension / version loaded.");

    }

    protected function execute(InputInterface $input, OutputInterface $output) 
    {
        while (true) {
            if(false === $this->isRunning) {
                if($build = $this->checkQueue() ) {
                    $this->startJob($build, $output);
                }
            }
            
            sleep($this->waitTime);
        }
    }
    
    protected function checkQueue()
    {
        $repo = $this->em->getRepository(Entity\VersionBuild::class);
        $build = $repo->findOneBy(['status'=>'waiting']);
        return $build;
    }
    
    protected function startJob($build, OutputInterface $output)
    {
        $this->isRunning = true;
        $command = "ssh boltrunner@bolt.rossriley.co.uk 'cap production docker:run package=".$build->package->name." version=".$build->version."'";
        $process = new Process($command);
        $process->mustRun();
        
        if ($process->isSuccessful()) {
            $response = $process->getOutput();
            $lines = explode("\n", $response);
            if( !isset($lines[2])) {
               continue; 
            }
            $build->status = "complete";
            $build->url = str_replace("0.0.0.0",$this->domain, $lines[2]);
            $build->lastrun = new \DateTime;
            $this->em->flush();
            $output->writeln("<info>Built ".$build->package->name." version ".$build->version." to ".$build->url."</info>");
        }
        $this->isRunning = false;
    }
    


}