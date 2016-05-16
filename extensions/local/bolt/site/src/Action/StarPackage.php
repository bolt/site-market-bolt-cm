<?php
namespace Bolt\Extension\Bolt\MarketPlace\Action;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\JsonResponse;
use Doctrine\ORM\EntityManager;
use Bolt\Extension\Bolt\MarketPlace\Entity;
use Aura\Router\Router;


class StarPackage
{

    public $em;
    public $router;   

    public function __construct(EntityManager $em, Router $router)
    {
        $this->em = $em;
        $this->router = $router;
    }
    
    public function __invoke(Request $request, $params)
    {
        $package = $params['package'];
        $repo = $this->em->getRepository(Entity\Package::class);
        $package = $repo->findOneBy(['id'=>$package]);
        
        $account = $this->em->find(Entity\Account::class, $request->getSession()->get("bolt.account.id"));
        
        $statRepo = $this->em->getRepository(Entity\Stat::class);
        $existing = $statRepo->findOneBy(['package'=>$package, 'account'=>$account]);
        
        if ($existing) {
            $request->getSession()->getFlashBag()->add('error', "Your have already starred this package");
        } else {
        
            $stat = new Entity\Stat([
                'source' => $request->server->get('HTTP_REFERER'),
                'ip' => $request->server->get('REMOTE_ADDR'),
                'recorded' => new \DateTime,
                'package' => $package,
                'type' => 'star',
                'account' => $account
            ]);
            
            $this->em->persist($stat);
            $this->em->flush();
            $request->getSession()->getFlashBag()->add('success', "Your have starred this package");
        }
        
        return new RedirectResponse($this->router->generate('view',['package'=>$package->id]));         
    }
}

