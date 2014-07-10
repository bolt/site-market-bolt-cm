<?php
namespace Bolt\Extensions\Action;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\RedirectResponse;

use Bolt\Extensions\Entity;


class Register extends AbstractAction
{
    
    public function __invoke(Request $request)
    {
        $form = $this->forms->create('account');
        
        return new Response($this->renderer->render("register.html", ['form'=>$form->createView()]));

    }
}