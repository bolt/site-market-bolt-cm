<?php
namespace Bolt\Extensions\Action;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\JsonResponse;
use Doctrine\ORM\EntityManager;
use Bolt\Extensions\Entity;


class Stat
{

    public $em;    

    public function __construct(EntityManager $em)
    {
        $this->em = $em;
    }
    
    public function __invoke(Request $request, $params)
    {
        $type = $params['id'];
        $package = $params['package'];
        $version = $params['version'];
        $repo = $this->em->getRepository(Entity\Package::class);
        
        $package = $repo->findOneBy(['name'=>$package]);
        
        $stat = new Entity\Stat([
            'source'=>$request->server->get('HTTP_REFERER'),
            'ip'=>$request->server->get('REMOTE_ADDR'),
            'recorded'=> new \DateTime,
            'package'=>$package,
            'version'=>$version,
            'type'=>$type
        ]);
        
        $this->em->persist($stat);
        $this->em->flush();
        
    
        $response = new JsonResponse(['status'=>'OK','package'=>$package->id]);
        $response->setCallback($request->get('callback'));
        return $response;
        
    }
}

