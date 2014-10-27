<?php
namespace Bolt\Extensions;
use Symfony\Component\HttpKernel\HttpKernelInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Doctrine\ORM\EntityManager;
use Aura\Router\Router;
use Bolt\Extensions\Entity;


class Firewall implements HttpKernelInterface
{
    /**
    * @var HttpKernelInterface
    */
    private $app;

    /**
    * @var EntityManager
    */
    private $em;
    
    /**
    * @var Router
    */
    private $router;
    
    
    /**
    * @var $restrict
    */
    private $restrict;
    
    /**
    * @var $accountUser
    */
    private $accountUser;
    
    
    /**
    * @param HttpKernelInterface $app
    * @param EntityManager $em
    */
    public function __construct(HttpKernelInterface $app, EntityManager $em, Router $router, $restrict = [])
    {
        $this->app = $app;
        $this->em = $em;
        $this->router = $router;
        $this->restrict = $restrict;
    }

    /**
    * {@inheritdoc}
    */

    public function handle(Request $request, $type = self::MASTER_REQUEST, $catch = true)
    {
        $this->checkUser($request);
        if (!$this->isAllowed($request)) {
            return new RedirectResponse($this->router->generate("login"));
        }
        return $this->app->handle($request, $type, $catch);
   
    }
    
    public function checkUser(Request $request)
    {
        $id = $request->getSession()->get("bolt.account.id");
        if (null !== $id) {
            $this->accountUser = $this->em->find(Entity\Account::class, $id);
            $request->attributes->set('isLoggedIn', true);
            $request->attributes->set('user', $this->accountUser);
            return true;
        }
    }
    
    public function isAllowed(Request $request)
    {
        $route = $request->attributes->get("route");
        if (isset($route['action']) && in_array($route['action'], $this->restrict)) {
            if (null !== $this->accountUser) {
                return true;
            }
            return false;
        }
        return true;
    }
} 
