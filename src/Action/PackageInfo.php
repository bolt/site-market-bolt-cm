<?php
namespace Bolt\Extensions\Action;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\JsonResponse;

use Bolt\Extensions\Entity;


class PackageInfo extends AbstractAction
{
    
    public function __invoke(Request $request, $params)
    {
        
        $p = $request->get('package');
        
        $repo = $this->em->getRepository(Entity\Package::class);
        $package = $repo->findOneBy(['approved'=>true, 'name'=>$p]);
        
        $vers = $this->packageManager->getVersions($package);
        
        print_r($vers); exit;        

        $response = new JsonResponse(['package'=>$package->serialize()]);
        $response->setCallback($request->get('callback'));
        return $response;
    }
}