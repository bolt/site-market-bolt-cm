<?php
namespace Bolt\Extensions\Action;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Twig_Environment;

use Bolt\Extensions\Entity;
use Bolt\Extensions\Command\TestExtension as TestCommand;
use Symfony\Component\Console\Input\ArrayInput;
use Symfony\Component\Console\Output\BufferedOutput;
use Symfony\Component\Process\Process;
use Goutte\Client;

class TestExtension extends AbstractAction
{
    
    public $renderer;

    
    public function __invoke(Request $request, $params)
    {
        $version = $request->get('version', 'dev-master');
        $package = $params['namespace']."/".$params['package'];
        
        $repo = $this->em->getRepository(Entity\Package::class);
        $p = $repo->findOneBy(['name'=>$package]);
        
        if (!$p) {
            die("No extension");
        }
        
        $repo = $this->em->getRepository(Entity\VersionBuild::class);
        $build = $repo->findOneBy(['package'=>$p->id, 'version'=>$version]);
        
        if (!$build) {
            
            $build = new Entity\VersionBuild;
            $build->package = $p;
            $build->version = $version;
            $build->status = 'waiting';
            $this->em->persist($build);
        }
        
        $this->em->flush(); 
        $tests = $this->testFunctionality($build);
        return new Response($this->renderer->render("extension-test.html", ['build'=>$build, 'tests'=>$tests]));

    }
    
    protected function testFunctionality($build)
    {
        $test = $build->getTestResult();
        $client = new Client();
        $crawler = $client->request('GET', $build->url.'/bolt');
        $form = $crawler->selectButton('Log on')->form();
        $crawler = $client->submit($form, array('username' => 'admin', 'password' => 'password'));
        $test[$build->url.'/bolt'] = [
            'title' => 'Can login to admin dashboard',
            'response'=> $client->getResponse()->getStatus(),
            'status' => $client->getResponse()->getStatus() == '200' ? "OK" : "FAIL"
        ];
        $build->testResult = json_encode($test);
        $this->em->flush();
    }
    
    

}