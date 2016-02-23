<?php
namespace Bolt\Extensions\Action;

use Aura\Router\Router;
use Bolt\Extensions\Service\BoltThemes;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Composer\Util\ConfigValidator;
use Composer\IO\NullIO;
use Twig_Environment;
use Doctrine\ORM\EntityManager;
use Bolt\Extensions\Entity;
use Bolt\Extensions\Service\PackageManager;



class ViewPackage
{

    public $renderer;
    public $em;
    public $packageManager;
    public $router;
    public $themeservice;

    public function __construct(Twig_Environment $renderer, EntityManager $em, PackageManager $packageManager, Router $router, BoltThemes $themeservice)
    {
        $this->renderer = $renderer;
        $this->em = $em;
        $this->packageManager = $packageManager;
        $this->router = $router;
        $this->themeservice = $themeservice;
    }

    public function __invoke(Request $request, $params)
    {
        $repo = $this->em->getRepository(Entity\Package::class);
        $package = $repo->findOneBy(['id'=>$params['package']]);

        if(!$package) {
            $request->getSession()->getFlashBag()->add('error', "There was a problem accessing this package");
            return new RedirectResponse($this->router->generate('profile'));
        }

        $allowedit = $package->account === $request->get('user');
        $readme = $this->packageManager->getReadme($package);

        return new Response(
            $this->renderer->render(
                "view.html",
                [
                    'package' => $package,
                    'readme' => $readme,
                    'allowedit' => $allowedit,
                    'boltthemes' => $this->themeservice->info($package)
                ]
            )
        );

    }
}
