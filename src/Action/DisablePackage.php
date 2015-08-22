<?php
namespace Bolt\Extensions\Action;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Twig_Environment;
use Doctrine\ORM\EntityManager;
use Bolt\Extensions\Entity;
use Aura\Router\Router;

class DisablePackage
{
    
    public $renderer;
    public $em;
    public $forms;
    
    public function __construct(EntityManager $em, Router $router)
    {
        $this->em = $em;
        $this->router = $router;
    }
    
    public function __invoke(Request $request, $params)
    {
        $repo = $this->em->getRepository(Entity\Package::class);
        $package = $repo->findOneBy(['id'=>$params['package'], 'account'=>$request->get('user')]);
        if(!$package) {
            $request->getSession()->getFlashBag()->add('error', "There was a problem accessing this package");
            return new RedirectResponse($this->router->generate('profile'));
        }
       
        $package->approved = false;
        $this->em->persist($package);
        $this->em->flush();

        $request->getSession()->getFlashBag()->add('info', "This extension has been disabled.");

        return new RedirectResponse($this->router->generate('profile'));


    }
}