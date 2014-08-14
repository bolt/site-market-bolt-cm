<?php
namespace Bolt\Extensions\Action;
use Symfony\Component\Form\FormFactory;
use Symfony\Component\Form\FormError;

use Doctrine\ORM\EntityManager;
use Twig_Environment;
use Aura\Router\Router;
use Bolt\Extensions\Service\PackageManager;

use Bolt\Extensions\Entity;


class AbstractAction
{
    
    public $renderer;
    public $forms;
    public $em;
    public $router;
    public $packageManager;
    
    public $accountUser;
    public $request;
    

    public function __construct(
        Twig_Environment $renderer, 
        FormFactory $forms, 
        EntityManager $em = null, 
        Router $router = null,
        PackageManager $packageManager = null
    )
    {
        $this->renderer = $renderer;
        $this->em = $em;
        $this->forms = $forms;
        $this->router = $router;
        $this->packageManager = $packageManager;
    }
    
    public function checkUser()
    {
        $id = $this->request->getSession()->get("bolt.account.id");
        if (null !== $id) {
            $this->accountUser = $this->em->find(Entity\Account::class, $id);
            $this->renderer->addGlobal('isLoggedIn', true);
            $this->renderer->addGlobal('user', $this->accountUser);
            return true;
        }
    }
    
    
    public function restrictAccess($request)
    {
        
        if (null !== $this->accountUser) {
            return true;
        }
        
        $request->getSession()->set('bolt.auth.return', $request->getPathInfo());
        return false;
    }
    
    public function setRequest($request)
    {
        $this->request = $request;
        $this->checkUser();
        $this->renderer->addGlobal('session', $request->getSession());
    }
    
    public function searchPackages($keyword)
    {
        $repo = $this->em->getRepository(Entity\Package::class);
        $packages = $repo->createQueryBuilder('p')
                ->where('p.approved = :status')
                ->andWhere('p.name LIKE :search OR p.title LIKE :search OR p.keywords LIKE :search')
                ->setParameter('status', true)
                ->setParameter('search', "%".$keyword."%")
                ->getQuery()
                ->getResult();
                
        return $packages;
    }
    
}