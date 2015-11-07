<?php
namespace Bolt\Extensions\Action;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\JsonResponse;
use Doctrine\ORM\EntityManager;
use Twig_Environment;
use Bolt\Extensions\Entity;


class JsonSearch
{
    
    public $em;
    public $renderer;
    
    public function __construct(Twig_Environment $renderer, EntityManager $em)
    {
        $this->em = $em;
        $this->renderer = $renderer;
    }
    
    public function __invoke(Request $request, $params)
    {
        $search = $request->get('q');
        $type = $request->get('type') ?: null;
        $order = $request->get('order') ?: null;
        $repo = $this->em->getRepository(Entity\Package::class);
        $packages = $repo->search($search, $type, $order);

        return new JsonResponse($packages);
    }
}