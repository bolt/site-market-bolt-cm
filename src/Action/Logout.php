<?php

namespace Bolt\Extensions\Action;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Aura\Router\Router;


class Logout
{
    
    public $router;
    
    public function __construct(Router $router)
    {
        $this->router = $router;
    }
    
    public function __invoke(Request $request)
    {
        $request->getSession()->remove("bolt.account.id");
        return new RedirectResponse($this->router->generate("login"));

    }
}