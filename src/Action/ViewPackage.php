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