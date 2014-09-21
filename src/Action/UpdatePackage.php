<?php
namespace Bolt\Extensions\Action;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\RedirectResponse;

use Bolt\Extensions\Entity;


class UpdatePackage extends AbstractAction
{
    
    public function __invoke(Request $request, $params)
    {
        $repo = $this->em->getRepository(Entity\Package::class);
        $package = $repo->findOneBy(['id'=>$params['package']]);
        try {
            $this->packageManager->validate($package, $this->accountUser->admin);
            $package = $this->packageManager->syncPackage($package);
            $request->getSession()->getFlashBag()->add('success', "Package ".$package->name." has been updated");
            if ($this->accountUser->approved) {
                $package->approved = true;
            }
        } catch (\Exception $e) {
            $request->getSession()->getFlashBag()->add('alert', "Package has an invalid composer.json and will be disabled!");
            $request->getSession()->getFlashBag()->add('warning', implode(" : ", $e->getTrace()));
            $package->approved = false; 
        }
        
        $this->em->flush();
        return new RedirectResponse($this->router->generate('profile'));

    }
}