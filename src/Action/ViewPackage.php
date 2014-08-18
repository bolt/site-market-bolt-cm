<?php
namespace Bolt\Extensions\Action;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\RedirectResponse;

use Bolt\Extensions\Entity;


class ViewPackage extends AbstractAction
{
    
    public function __invoke(Request $request, $params)
    {
        $repo = $this->em->getRepository(Entity\Package::class);
        $package = $repo->findOneBy(['id'=>$params['package']]);
        try {
           $info = $this->packageManager->getVersions($package); 
        } catch (\Exception $e) {
            $request->getSession()->getFlashBag()->add('alert', $e->getMessage());
        }
       
        return new Response($this->renderer->render("view.html", ['package'=>$package]));

    }
}