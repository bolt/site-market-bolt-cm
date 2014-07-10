<?php
namespace Bolt\Extensions\Action;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

use Bolt\Extensions\Entity;


class Submitted extends AbstractAction
{
    
    public function __invoke(Request $request)
    {
        return new Response($this->renderer->render("submitted.html"));

    }
}