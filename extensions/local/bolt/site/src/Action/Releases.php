<?php
namespace Bolt\Extension\Bolt\MarketPlace\Action;

use Aura\Router\Router;
use Bolt\Extension\Bolt\MarketPlace\Entity;
use Bolt\Extension\Bolt\MarketPlace\Service\PackageManager;
use DateTime;
use Doctrine\ORM\EntityManager;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Twig_Environment;

class Releases
{
    public $renderer;
    public $em;
    public $packageManager;
    public $router;

    public function __construct(Twig_Environment $renderer, EntityManager $em, PackageManager $packageManager, Router $router)
    {
        $this->renderer = $renderer;
        $this->em = $em;
        $this->packageManager = $packageManager;
        $this->router = $router;
    }

    public function __invoke(Request $request, $params)
    {
        $repo = $this->em->getRepository(Entity\Package::class);
        $package = $repo->findOneBy(['id' => $params['package']]);

        if (!$package) {
            $request->getSession()->getFlashBag()->add('error', 'There was a problem accessing this package');

            return new RedirectResponse($this->router->generate('profile'));
        }

        $versions = ['dev' => [], 'stable' => []];

        try {
            $repo = $this->em->getRepository(Entity\VersionBuild::class);
            $info = $this->packageManager->getInfo($package, false);
            $i = 0;
            foreach ($info as $ver) {
                if ($i == 0) {
                    $package->updated = new DateTime($ver['time']);
                }
                $build = $repo->findOneBy(['package' => $package->id, 'version' => $ver['version']]);
                if ($build) {
                    $ver['build'] = $build;
                }
                $versions[$ver['stability']][] = $ver;
                $i++;
            }
            $this->em->flush();
        } catch (\Exception $e) {
            $request->getSession()->getFlashBag()->add('error', $e->getMessage());
        }

        return new Response(
            $this->renderer->render(
                'releases.twig',
                [
                    'package'  => $package,
                    'versions' => $versions,
                ]
            )
        );
    }
}
