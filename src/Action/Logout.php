<?php

namespace Bolt\Extensions\Action;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RedirectResponse;


class Logout extends AbstractAction
{
    
    
    public function __invoke(Request $request)
    {
        $request->getSession()->remove("bolt.account.id");
        return new RedirectResponse($this->router->generate("login"));

    }
}