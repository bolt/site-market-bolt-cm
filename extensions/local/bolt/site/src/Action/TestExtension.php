<?php

namespace Bolt\Extension\Bolt\MarketPlace\Action;

use Bolt\Extension\Bolt\MarketPlace\Storage\Entity;
use Bolt\Extension\Bolt\MarketPlace\Storage\Repository;
use Bolt\Storage\EntityManager;
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
        $packageId = $params['package'];
        $retest = isset($params['retest']) ? true : false;

        /** @var EntityManager $em */
        $em = $this->getAppService('storage');
        /** @var Repository\Package $packageRepo */
        $packageRepo = $em->getRepository(Entity\Package::class);
        $package = $packageRepo->findOneBy(['id' => $packageId]);

        if (!$package) {
            throw new \InvalidArgumentException('The extension provided does not exist', 1);
        }

        /** @var Repository\VersionBuild $versionBuildRepo */
        $versionBuildRepo = $em->getRepository(Entity\VersionBuild::class);
        /** @var Entity\VersionBuild $build */
        $build = $versionBuildRepo->findOneBy(['package_id' => $package->getId(), 'version' => $version]);

        if (!$build) {
            $build = new Entity\VersionBuild();
            $build->setPackageId($packageId);
            $build->setVersion($version);
            $build->setStatus('waiting');

            $versionBuildRepo->save($build);
        }

        if ($retest) {
            $build->setPhpTarget($request->request->get('phpTarget'));
            $build->setStatus('waiting');
            $build->setTestStatus('pending');
            $build->setTestResult(null);
            $build->setUrl(null);

            $versionBuildRepo->save($build);
        }

        /** @var \Twig_Environment $twig */
        $twig = $this->getAppService('twig');
        $context = [
            'build'   => $build,
            'package' => $package,
            'tests'   => $this->getTestStatus($versionBuildRepo, $build),
        ];

        $html = $twig->render('extension-test.twig', $context);

        return new Response($html);
    }

    /**
     * @param Repository\VersionBuild $repo
     * @param Entity\VersionBuild     $build
     *
     * @return array
     */
    protected function getTestStatus(Repository\VersionBuild $repo, Entity\VersionBuild $build)
    {
        $tests = [];
        if ($build->getUrl()) {
            $canConnect = true;
            try {
                $client = $this->getAppService('guzzle.client');
                $client->get($build->getUrl());
            } catch (RequestException $e) {
                if ($e->getCode() == Response::HTTP_BAD_GATEWAY) {
                    $canConnect = false;
                    $build->setStatus('complete');
                    $build->setTestStatus('failed');
                } else {
                    $canConnect = false;
                    $build->setStatus('waiting');
                    $build->setTestStatus('pending');
                    $build->setTestResult(null);
                }

                $repo->save($build);
            }

            if ($canConnect) {
                try {
                    $tests = $this->testFunctionality($build);
                    $build->setStatus('complete');
                } catch (\Exception $e) {
                    $build->setStatus('failed');
                }

                $repo->save($build);
            }
        }

        return $tests;
    }

    /**
     * @param Entity\VersionBuild $build
     *
     * @return array
     */
    protected function testFunctionality(Entity\VersionBuild $build)
    {
        $test = [];
        $test = array_merge($test, $this->testDashboard($build));
        $test = array_merge($test, $this->testHomepage($build));
        $test = array_merge($test, $this->testExtensionLoaded($build));

        $build->setTestResult($test);
        $this->approvedStatus($build);

        return $test;
    }

    protected function testHomepage(Entity\VersionBuild $build)
    {
        $client = new Client();
        $client->request('GET', $build->getUrl() . '/');

        $test['homepage'] = [
            'title'    => 'Can load site home page',
            'response' => $client->getResponse()->getStatus(),
            'status'   => $client->getResponse()->getStatus() === Response::HTTP_OK ? 'OK' : 'FAIL',
        ];

        return $test;
    }

    /**
     * @param Entity\VersionBuild $build
     *
     * @return array
     */
    protected function testDashboard(Entity\VersionBuild $build)
    {
        $client = new Client();
        $crawler = $client->request('GET', $build->getUrl() . '/bolt');
        $form = $crawler->selectButton('Log on')->form();
        $client->submit($form, ['username' => 'admin', 'password' => 'password']);

        $test['dashboard'] = [
            'title'    => 'Can login to admin dashboard',
            'response' => $client->getResponse()->getStatus(),
            'status'   => $client->getResponse()->getStatus() === Response::HTTP_OK ? 'OK' : 'FAIL',
        ];

        if (strpos($client->getRequest()->getUri(), 'login') !== false) {
            $test['dashboard']['status'] = 'FAIL';
        }

        return $test;
    }

    /**
     * @param Entity\VersionBuild $build
     *
     * @return array
     */
    protected function testExtensionLoaded(Entity\VersionBuild $build)
    {
        $test['extension'] = [
            'title'  => 'Extension is loaded, appears in installed list',
            'status' => 'FAIL',
        ];
        $client = new Client();
        $crawler = $client->request('GET', $build->getUrl() . '/bolt');
        $form = $crawler->selectButton('Log on')->form();

        $client->submit($form, ['username' => 'admin', 'password' => 'password']);
        $client->request('GET', $build->getUrl() . '/bolt/extend/installed');

        try {
            $json = $client->getResponse()->getContent()->getContents();
            $test['extension']['response'] = $client->getResponse()->getStatus();
            $test['extension']['raw_response'] = $json;
            $extensions = json_decode($json, true);

            /** @var Repository\Package $packageRepo */
            $packageRepo = $this->getAppService('storage')->getRepository(Entity\Package::class);
            foreach ($extensions['installed'] as $ext) {
                $package = $packageRepo->find($build->getPackageId());
                if ($ext['name'] === $package->getName() && $ext['version'] === $build->getVersion()) {
                    $test['extension']['status'] = $client->getResponse()->getStatus() === Response::HTTP_OK ? 'OK' : 'FAIL';
                }
            }
        } catch (\Exception $e) {
            return $test;
        }

        return $test;
    }

    /**
     * @param Entity\VersionBuild $build
     */
    protected function approvedStatus(Entity\VersionBuild $build)
    {
        if (!count($build->getTestResult())) {
            $build->setUrl('pending');

            return;
        }
        $status = 'approved';
        foreach ($build->getTestResult() as $test) {
            if ($test['status'] !== 'OK') {
                $status = 'failed';
            }
        }
        $build->setUrl($status);
    }
}
