<?php
namespace Bolt\Extensions\Action;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\RedirectResponse;

use Bolt\Extensions\Entity;


class Home extends AbstractAction
{
    
    public function __invoke(Request $request)
    {
        
        return new Response($this->renderer->render("index.html", []));

    }
}