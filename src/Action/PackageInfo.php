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
        $bolt = $request->get("bolt");
        
        $repo = $this->em->getRepository(Entity\Package::class);
        $package = $repo->findOneBy(['approved'=>true, 'name'=>$p]);
        
        $allVersions = $this->packageManager->getInfo($package, $bolt);
        $buildRepo = $this->em->getRepository(Entity\VersionBuild::class);

        foreach($allVersions as &$version) {
            $build = $buildRepo->findOneBy(['package'=>$package->id, 'version'=>$version['version']]);
            if ($build) {
                $version['buildStatus'] = $build->testStatus;
            } else {
                $version['buildStatus'] = 'untested';
            }
        }                

        $response = new JsonResponse(['package'=>$package->serialize(),'version'=>$allVersions]);
        $response->setCallback($request->get('callback'));
        return $response;
    }
}