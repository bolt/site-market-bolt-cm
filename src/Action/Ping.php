<?php
namespace Bolt\Extensions\Action;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Twig_Environment;
use Doctrine\ORM\EntityManager;

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
