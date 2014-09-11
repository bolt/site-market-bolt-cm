<?php
namespace Bolt\Extensions\Action;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\JsonResponse;

use Bolt\Extensions\Entity;


class Ping extends AbstractAction
{
    
    public function __invoke(Request $request, $params)
    {
        $type = $params['id'];
        $package = $params['package'];
        $repo = $this->em->getRepository(Entity\Package::class);
        
        $package = $repo->findOneBy(['id'=>$package]);
        
        $stat = new Entity\Stat([
            'source'=>$request->server->get('HTTP_REFERER'),
            'ip'=>$request->server->get('REMOT_ADDR'),
            'recorded'=> new \DateTime,
            'package'=>$package,
            'type'=>$type
        ]);
        
        $this->em->persist($stat);
        $this->em->flush();
        
    
        $response = new JsonResponse(['status'=>'OK','package'=>$package->id]);
        $response->setCallback($request->get('callback'));
        return $response;
        
    }
}

