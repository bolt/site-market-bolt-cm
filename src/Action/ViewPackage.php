<?php
namespace Bolt\Extensions\Action;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Composer\Util\ConfigValidator;
use Composer\IO\NullIO;
use Bolt\Extensions\Entity;


class ViewPackage extends AbstractAction
{
    
    public function __invoke(Request $request, $params)
    {
        $repo = $this->em->getRepository(Entity\Package::class);
        $package = $repo->findOneBy(['id'=>$params['package']]);
        $versions = [];
        
        $allowedit = $package->account === $this->accountUser;        

        try {
            $repo = $this->em->getRepository(Entity\VersionBuild::class);
            $info = $this->packageManager->getInfo($package, "2.0.0");
            foreach($info as $ver) {
                $build = $repo->findOneBy(['package'=>$package->id, 'version'=>$ver['version']]);
                if($build) {
                    $ver['build'] = $build;
                } 
                $versions[$ver['stability']][] = $ver;
            }
        } catch (\Exception $e) {
            $request->getSession()->getFlashBag()->add('alert', $e->getMessage());
        }
               
        return new Response($this->renderer->render("view.html", ['package' => $package, 'versions' => $versions, 'allowedit' => $allowedit]));

    }
}