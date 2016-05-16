<?php
namespace Bolt\Extension\Bolt\MarketPlace\Action;

use Bolt\Extension\Bolt\MarketPlace\Entity;
use Doctrine\ORM\EntityManager;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Twig_Environment;

class Feed
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
        $repo = $this->em->getRepository(Entity\Package::class);
        $packages = $repo->search(null, null, 'date');

        $response = new Response($this->renderer->render('feed.xml', ['packages' => $packages]));
        $response->headers->set('Content-Type', 'text/xml');

        return $response;
    }
}
