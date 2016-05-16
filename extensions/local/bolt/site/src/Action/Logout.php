<?php

namespace Bolt\Extension\Bolt\MarketPlace\Action;

use Aura\Router\Router;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;

class Logout
{
    public $router;
    
    public function __construct(Router $router)
    {
        $this->router = $router;
    }
    
    public function __invoke(Request $request)
    {
        $request->getSession()->remove('bolt.account.id');

        return new RedirectResponse($this->router->generate('login'));
    }
}
