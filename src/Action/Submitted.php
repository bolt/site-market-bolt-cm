<?php
namespace Bolt\Extensions\Action;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Twig_Environment;
use Bolt\Extensions\Entity;


class Submitted
{
    
    public $renderer;
    
    public function __construct(Twig_Environment $renderer)
    {
        $this->renderer = $renderer;
    }
    
    public function __invoke(Request $request)
    {
        return new Response($this->renderer->render("submitted.twig"));

    }
}