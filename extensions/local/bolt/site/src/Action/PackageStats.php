<?php
namespace Bolt\Extension\Bolt\MarketPlace\Action;

use Aura\Router\Router;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Twig_Environment;
use Doctrine\ORM\EntityManager;
use Symfony\Component\Form\FormFactory;
use Bolt\Extension\Bolt\MarketPlace\Entity;


class PackageStats
{

    public $renderer;
    public $em;
    public $forms;
    public $router;

    public function __construct(Twig_Environment $renderer, EntityManager $em, Router $router)
    {
        $this->renderer = $renderer;
        $this->em = $em;
        $this->router = $router;
    }

    public function __invoke(Request $request, $params)
    {
        $repo = $this->em->getRepository(Entity\Package::class);
        $package = $repo->findOneBy(['id'=>$params['package'], 'account'=>$request->get('user')]);
        //$package = $repo->findOneBy(['id'=>$params['package']]);

        if(!$package) {
            $request->getSession()->getFlashBag()->add('error', "There was a problem accessing this package");
            return new RedirectResponse($this->router->generate('profile'));
        }

        return new Response(
            $this->renderer->render(
                "stats.twig",
                [
                    'package' => $package
                ]
            ));


    }
}
