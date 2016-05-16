<?php
namespace Bolt\Extension\Bolt\MarketPlace\Action;

use Doctrine\ORM\EntityManager;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Twig_Environment;

class Ping
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
        return new Response('pong', Response::HTTP_OK);
    }
}
