<?php
namespace Bolt\Extensions\Action;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Twig_Environment;
use Doctrine\ORM\EntityManager;
use Bolt\Extensions\Entity;
use Bolt\Extensions\Command\TestExtension as TestCommand;
use Symfony\Component\Console\Input\ArrayInput;
use Symfony\Component\Console\Output\BufferedOutput;
use Symfony\Component\Process\Process;
use Goutte\Client;
use GuzzleHttp\Client as Guzzle;
use GuzzleHttp\Exception\RequestException;


class TestExtension
{
    
    public $renderer;
    public $em;
    
    public function __construct(Twig_Environment $renderer, EntityManager $em)
    {
        $this->renderer = $renderer;
        $this->em = $em;
    }
    
    
    public function __invoke(Request $request, $params)
    {
        $version = $params['version'];
        $package = $params['package'];
        $retest = isset($params['retest']) ? true : false;
       
        
        $repo = $this->em->getRepository(Entity\Package::class);
        $p = $repo->findOneBy(['id'=>$package]);
        
        if (!$p) {
            throw new \InvalidArgumentException("The extension provided does not exist", 1);
        }
        
        $repo = $this->em->getRepository(Entity\VersionBuild::class);
        $build = $repo->findOneBy(['package'=>$p->id, 'version'=>$version]);
        
        if (!$build) {
            $build = new Entity\VersionBuild;
            $build->package = $p;
            $build->version = $version;
            $build->status = 'waiting';
            $this->em->persist($build);
            $this->em->flush();
        }
        
        if ($retest) {
            if($request->request->get('phpTarget')) {
                $build->phpTarget = $request->request->get('phpTarget');
            }
            $build->status = 'waiting';
            $build->testStatus = 'pending';
            $build->testResult = '';
            $build->url = '';
            $this->em->flush();
        }

        
        $tests = [];
        if($build->url) {
            $canConnect = true;
            try {
                $client = new Guzzle(['base_url' => $build->url]);
                $response = $client->get('/');
            } catch (RequestException $e) {
                if($e->getCode() == '502') {
                    $canConnect = false;
                    $build->status = 'complete';
                    $build->testStatus = 'failed';
                } else {
                    $canConnect = false;
                    $build->status = 'waiting';
                    $build->testStatus = 'pending';
                    $build->testResult = '';
                }
                $this->em->flush();

                
            }
            
            if ($canConnect) {
                try {
                   $tests = $this->testFunctionality($build); 
                } catch (\Exception $e) {
                    $build->status = 'failed';
                    $this->em->flush();
                }
                $build->status = 'complete';
                $this->em->flush();

            }
            
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
        $crawler = $client->request('GET', $build->url.'/');
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

        if ($client->getRequest()->getUri() !== $build->url.'/bolt') {
           $test['dashboard']['status'] = "FAIL"; 
        }
        return $test;
    }
    
    protected function testExtensionLoaded($build)
    {
        $test['extension'] = [
            'title' => 'Extension is loaded, appears in installed list',
            'status' => 'FAIL'
        ];
        $client = new Client();
        $crawler = $client->request('GET', $build->url.'/bolt');
        $form = $crawler->selectButton('Log on')->form();
        $crawler = $client->submit($form, array('username' => 'admin', 'password' => 'password'));
        $crawler = $client->request('GET', $build->url.'/bolt/extend/installed');
        try {
            $json = $client->getResponse()->getContent()->getContents();
            $extensions = json_decode($json, true);
            foreach ($extensions['installed'] as $ext) {
                if ($ext['name'] === $build->package->name && $ext['version'] === $build->version) {
                    $test['extension']['response'] = $client->getResponse()->getStatus();
                    $test['extension']['status'] = $client->getResponse()->getStatus() == '200' ? "OK" : "FAIL";
                }    
            }
            
        } catch (\Exception $e) {
            return $test;
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