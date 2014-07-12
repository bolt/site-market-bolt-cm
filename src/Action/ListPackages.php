<?php
namespace Bolt\Extensions\Action;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\RedirectResponse;

use Bolt\Extensions\Entity;


class ListPackages extends AbstractAction
{
    
    public function __invoke(Request $request, $params)
    {
        
        $repo = $this->em->getRepository(Entity\Package::class);
        $packages = $repo->findBy(['approved'=>true]);
        array_walk($packages, function(&$v, $k){
            $v = $v->serialize();
            unset($v['approved']);
        });
        return new Response(json_encode($packages, JSON_PRETTY_PRINT));
    }
}