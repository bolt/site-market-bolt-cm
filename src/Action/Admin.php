<?php
namespace Bolt\Extensions\Action;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Twig_Environment;
use Doctrine\ORM\EntityManager;
use Bolt\Extensions\Entity;


class Admin
{
    
    public $renderer;
    public $em;
    
    public function __construct(Twig_Environment $renderer, EntityManager $em)
    {
        $this->renderer = $renderer;
        $this->em = $em;
    }
    
    public function __invoke(Request $request)
    {
        $repo = $this->em->getRepository(Entity\Package::class);
        $packages = $repo->findAll();
        return new Response($this->renderer->render("admin.html", ['packages'=>$packages]));

    }
}