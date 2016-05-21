<?php

namespace Bolt\Extension\Bolt\MarketPlace\Action;

use Bolt\Extension\Bolt\MarketPlace\Storage\Entity;
use Bolt\Extension\Bolt\MarketPlace\Service\PackageManager;
use Bolt\Storage\EntityManager;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Session\Session;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

class Tests extends AbstractAction
{
    /**
     * {@inheritdoc}
     */
    public function execute(Request $request, array $params)
    {
        /** @var UrlGeneratorInterface $urlGen */
        $urlGen = $this->getAppService('url_generator');
        /** @var Session $session */
        $session = $this->getAppService('session');
        /** @var EntityManager $em */
        $em = $this->getAppService('storage');
        $repo = $em->getRepository(Entity\Package::class);
        $services = $this->getAppService('marketplace.services');
        /** @var PackageManager $packageManager */
        $packageManager = $services['package_manager'];

        $package = $repo->findOneBy(['id' => $params['package'], 'account' => $request->get('user')]);
        $allowedit = $package->account === $request->get('user');

        if (!$package || !$allowedit) {
            $session->getFlashBag()->add('error', 'There was a problem accessing this package');
            $route = $urlGen->generate('profile');

            return new RedirectResponse($route);
        }

        try {
            $repo = $em->getRepository(Entity\VersionBuild::class);
            $info = $packageManager->getInfo($package, false);
            foreach ($info as $ver) {
                $build = $repo->findOneBy(['package' => $package->id, 'version' => $ver['version']]);
                if ($build) {
                    $ver['build'] = $build;
                }
                $versions[$ver['stability']][] = $ver;
            }
        } catch (\Exception $e) {
            $session->getFlashBag()->add('error', $e->getMessage());
        }


        /** @var \Twig_Environment $twig */
        $twig = $this->getAppService('twig');
        $context = [
            'package'  => $package,
            'versions' => $versions,
        ];
        $html = $twig->render('tests.twig', $context);

        return new Response($html);
    }
}
