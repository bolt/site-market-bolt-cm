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
        
        // @todo: There must be a better way to do this..
        $packageowner = $repo->findOneBy(['id'=>$params['package'], 'account'=>$this->accountUser]);
        if($packageowner) {
            $allowedit = true;
        } else {
            $allowedit = false;
        }

        try {
            $info = $this->packageManager->getInfo($package, "2.0.0");
            foreach($info as $ver) {
                $versions[$ver['stability']][] = $ver;
            }
        } catch (\Exception $e) {
            $request->getSession()->getFlashBag()->add('alert', $e->getMessage());
        }     
       
        return new Response($this->renderer->render("view.html", ['package' => $package, 'versions' => $versions, 'allowedit' => $allowedit]));

    }
}