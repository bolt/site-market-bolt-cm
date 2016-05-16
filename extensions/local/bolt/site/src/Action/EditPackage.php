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


class EditPackage
{

    public $renderer;
    public $em;
    public $forms;
    public $router;

    public function __construct(Twig_Environment $renderer, EntityManager $em, FormFactory $forms, Router $router)
    {
        $this->renderer = $renderer;
        $this->em = $em;
        $this->forms = $forms;
        $this->router = $router;
    }

    public function __invoke(Request $request, $params)
    {
        $repo = $this->em->getRepository(Entity\Package::class);
        $package = $repo->findOneBy(['id'=>$params['package'], 'account'=>$request->get('user')]);
        if (!$package->token) {
            $package->regenerateToken();
            $this->em->flush();
        }
        if(!$package) {
            $request->getSession()->getFlashBag()->add('error', "There was a problem accessing this package");
            return new RedirectResponse($this->router->generate('profile'));
        }

        $form = $this->forms->create('package', $package);
        $form->handleRequest();

        if ($form->isValid()) {
            $this->em->persist($package);
            $this->em->flush();
            $request->getSession()->getFlashBag()->add('success', "Your package was succesfully updated");

        }

        return new Response(
            $this->renderer->render(
                "submit.twig",
                [
                    'form'=>$form->createView(),
                    'hook' => ($package->token) ? 'https://'.$request->server->get('HTTP_HOST') . $this->router->generate('hook').'?token='.$package->token : false,
                    'package' => $package
                ]
            ));


    }
}
