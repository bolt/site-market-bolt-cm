<?php

namespace Bolt\Extension\Bolt\MarketPlace\Action;

use Bolt\Extension\Bolt\MarketPlace\Storage\Entity;
use Bolt\Extension\Bolt\MarketPlace\Storage\Repository\Package;
use Bolt\Storage\EntityManager;
use Bolt\Storage\Repository;
use Goutte\Client;
use GuzzleHttp\Exception\RequestException;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;


class TestExtension extends AbstractAction
{
    /**
     * {@inheritdoc}
     */
    public function execute(Request $request, array $params)
    {
        $version = $params['version'];
        $package = $params['package'];
        $retest = isset($params['retest']) ? true : false;

        /** @var EntityManager $em */
        $em = $this->getAppService('storage');
        /** @var Package $repo */
        $repo = $em->getRepository(Entity\Package::class);
        $p = $repo->findOneBy(['id' => $package]);

        if (!$p) {
            throw new \InvalidArgumentException('The extension provided does not exist', 1);
        }

        /** @var Repository $repo */
        $repo = $em->getRepository(Entity\VersionBuild::class);
        $build = $repo->findOneBy(['package' => $p->id, 'version' => $version]);

        if (!$build) {
            $build = new Entity\VersionBuild();
            $build->package = $p;
            $build->version = $version;
            $build->status = 'waiting';
            $repo->save($build);
        }

        if ($retest) {
            if ($request->request->get('phpTarget')) {
                $build->phpTarget = $request->request->get('phpTarget');
            }
            $build->status = 'waiting';
            $build->testStatus = 'pending';
            $build->testResult = '';
            $build->url = '';
//@TODO Finish this
$this->em->flush();
        }


        $tests = [];
        if ($build->url) {
            $canConnect = true;
            try {
                $client = $this->getAppService('guzzle.client');
                $response = $client->get($build->url);
            } catch (RequestException $e) {
                if ($e->getCode() == Response::HTTP_BAD_GATEWAY) {
                    $canConnect = false;
                    $build->status = 'complete';
                    $build->testStatus = 'failed';
                } else {
                    $canConnect = false;
                    $build->status = 'waiting';
                    $build->testStatus = 'pending';
                    $build->testResult = '';
                }
//@TODO Finish this
$this->em->flush();
            }

            if ($canConnect) {
                try {
                    $tests = $this->testFunctionality($build);
                } catch (\Exception $e) {
                    $build->status = 'failed';
//@TODO Finish this
$this->em->flush();
                }
                $build->status = 'complete';
//@TODO Finish this
$this->em->flush();
            }
        }

        /** @var \Twig_Environment $twig */
        $twig = $this->getAppService('twig');
        $context = [
            'build'   => $build,
            'tests'   => $tests,
            'package' => $p,
        ];
        $html = $twig->render('extension-test.twig', $context);

        return new Response($html);
    }

    protected function testFunctionality($build)
    {
        $test = [];
        $test = array_merge($test, $this->testDashboard($build));
        $test = array_merge($test, $this->testHomepage($build));
        $test = array_merge($test, $this->testExtensionLoaded($build));


        $build->testResult = json_encode($test);
        $this->approvedStatus($build);
//@TODO Finish this
$this->em->flush();
    }

    protected function testHomepage($build)
    {
        $client = new Client();
        $crawler = $client->request('GET', $build->url . '/');
        $test['homepage'] = [
            'title'    => 'Can load site home page',
            'response' => $client->getResponse()->getStatus(),
            'status'   => $client->getResponse()->getStatus() == '200' ? 'OK' : 'FAIL',
        ];

        return $test;
    }

    protected function testDashboard($build)
    {
        $client = new Client();
        $crawler = $client->request('GET', $build->url . '/bolt');
        $form = $crawler->selectButton('Log on')->form();
        $crawler = $client->submit($form, ['username' => 'admin', 'password' => 'password']);
        $test['dashboard'] = [
            'title'    => 'Can login to admin dashboard',
            'response' => $client->getResponse()->getStatus(),
            'status'   => $client->getResponse()->getStatus() == '200' ? 'OK' : 'FAIL',
        ];

        if (strpos($client->getRequest()->getUri(), 'login') !== false) {
            $test['dashboard']['status'] = 'FAIL';
        }

        return $test;
    }

    protected function testExtensionLoaded($build)
    {
        $test['extension'] = [
            'title'  => 'Extension is loaded, appears in installed list',
            'status' => 'FAIL',
        ];
        $client = new Client();
        $crawler = $client->request('GET', $build->url . '/bolt');
        $form = $crawler->selectButton('Log on')->form();
        $crawler = $client->submit($form, ['username' => 'admin', 'password' => 'password']);
        $crawler = $client->request('GET', $build->url . '/bolt/extend/installed');
        try {
            $json = $client->getResponse()->getContent()->getContents();
            $test['extension']['response'] = $client->getResponse()->getStatus();
            $test['extension']['raw_response'] = $json;
            $extensions = json_decode($json, true);
            foreach ($extensions['installed'] as $ext) {
                if ($ext['name'] === $build->package->name && $ext['version'] === $build->version) {
                    $test['extension']['status'] = $client->getResponse()->getStatus() == '200' ? 'OK' : 'FAIL';
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
            $build->url = 'pending';
            return;
        }
        $status = 'approved';
        foreach ($build->testResult as $test) {
            if ($test['status'] !== 'OK') {
                $status = 'failed';
            }
        }
        $build->url = $status;
    }
}
