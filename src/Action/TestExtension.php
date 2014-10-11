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
        $package = $params['package'];
        
        $repo = $this->em->getRepository(Entity\Package::class);
        $p = $repo->findOneBy(['id'=>$package]);
        
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
        $tests = [];
        if($build->url) {
            $tests = $this->testFunctionality($build);
        }
        return new Response($this->renderer->render("extension-test.html", ['build'=>$build, 'tests'=>$tests]));

    }
    
    protected function testFunctionality($build)
    {
        $test = [];
        $test = array_merge($test, $this->testDashboard($build));
        $test = array_merge($test, $this->testHomepage($build));
        $test = array_merge($test, $this->testExtensionLoaded($build));
        
        
        $build->testResult = json_encode($test);
        $this->approvedStatus($build);
        $this->em->flush();
    }
    
    protected function testHomepage($build)
    {
        $client = new Client();
        $crawler = $client->request('GET', $build->url.'/bolt');
        $test['homepage'] = [
            'title' => 'Can load site home page',
            'response'=> $client->getResponse()->getStatus(),
            'status' => $client->getResponse()->getStatus() == '200' ? "OK" : "FAIL"
        ];
        return $test;
    }
    
    protected function testDashboard($build)
    {
        $client = new Client();
        $crawler = $client->request('GET', $build->url.'/bolt');
        $form = $crawler->selectButton('Log on')->form();
        $crawler = $client->submit($form, array('username' => 'admin', 'password' => 'password'));
        $test['dashboard'] = [
            'title' => 'Can login to admin dashboard',
            'response'=> $client->getResponse()->getStatus(),
            'status' => $client->getResponse()->getStatus() == '200' ? "OK" : "FAIL"
        ];

        if ($client->getRequest()->getUri() !== $build->url.'/bolt/') {
           $test['dashboard']['status'] = "FAIL"; 
        }
        return $test;
    }
    
    protected function testExtensionLoaded($build)
    {
        $client = new Client();
        $crawler = $client->request('GET', $build->url.'/bolt');
        $form = $crawler->selectButton('Log on')->form();
        $crawler = $client->submit($form, array('username' => 'admin', 'password' => 'password'));
        $crawler = $client->request('GET', $build->url.'/bolt/extend/installed');
        try {
            $json = $client->getResponse()->getContent()->getContents();
            $extensions = json_decode($json, true);
            foreach ($extensions as $ext) {
                if ($ext['name'] === $build->package->name && $ext['version'] === $build->version) {
                    $test['extension'] = [
                        'title' => 'Extension is loaded, appears in installed list',
                        'response'=> $client->getResponse()->getStatus(),
                        'status' => $client->getResponse()->getStatus() == '200' ? "OK" : "FAIL"
                    ];
                }    
            }
            
        } catch (\Exception $e) {
            $test['extension'] = [
                'title' => 'Extension is loaded, appears in installed list',
                'status' => 'FAIL'
            ];
        }
        
        return $test;
    }
    
    protected function approvedStatus($build)
    {
        if (!count($build->testResult)) {
            return 'pending';
        }
        $status = 'approved';
        foreach($build->testResult as $test) {
            if ($test['status'] !== 'OK' ) {
                $status = 'failed';
            }
        }
        $build->testStatus = $status;
    }
    
    

}