<?php
namespace Bolt\Extensions\Action;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Doctrine\ORM\EntityManager;
use Bolt\Extensions\Entity;
use Twig_Environment;


class V3Ready
{

    public $em;
    public $renderer;

    public function __construct(EntityManager $em, Twig_Environment $renderer)
    {
        $this->em = $em;
        $this->renderer = $renderer;
    }

    public function __invoke(Request $request, $params)
    {
        $repo = $this->em->getRepository(Entity\Package::class);

        $packages = $repo->findBy([],['title' => 'ASC']);

        return new Response($this->renderer->render("v3ready.twig", ['packages' => $packages]));

    }
}

