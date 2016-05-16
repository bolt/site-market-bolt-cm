<?php

namespace Bolt\Extension\Bolt\MarketPlace\Action;

use Aura\Router\Router;
use Bolt\Extension\Bolt\MarketPlace\Entity;
use Bolt\Extension\Bolt\MarketPlace\Service\PackageManager;
use Doctrine\ORM\EntityManager;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Twig_Environment;

class Tests
{
    public $renderer;
    public $em;
    public $router;

    public function __construct(Twig_Environment $renderer, EntityManager $em, Router $router, PackageManager $packageManager)
    {
        $this->renderer = $renderer;
        $this->em = $em;
        $this->router = $router;
        $this->packageManager = $packageManager;
    }

    public function __invoke(Request $request, $params)
    {
        $repo = $this->em->getRepository(Entity\Package::class);
        $package = $repo->findOneBy(['id' => $params['package'], 'account' => $request->get('user')]);
        $allowedit = $package->account === $request->get('user');

        if (!$package || !$allowedit) {
            $request->getSession()->getFlashBag()->add('error', 'There was a problem accessing this package');

            return new RedirectResponse($this->router->generate('profile'));
        }
        
        try {
            $repo = $this->em->getRepository(Entity\VersionBuild::class);
            $info = $this->packageManager->getInfo($package, false);
            foreach ($info as $ver) {
                $build = $repo->findOneBy(['package' => $package->id, 'version' => $ver['version']]);
                if ($build) {
                    $ver['build'] = $build;
                }
                $versions[$ver['stability']][] = $ver;
            }
        } catch (\Exception $e) {
            $request->getSession()->getFlashBag()->add('error', $e->getMessage());
        }

        return new Response($this->renderer->render('tests.twig', [
            'package'  => $package,
            'versions' => $versions,
        ]));
    }
}
