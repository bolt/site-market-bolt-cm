<?php
namespace Bolt\Extensions\Action;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\RedirectResponse;

use Bolt\Extensions\Entity;


class EditPackage extends AbstractAction
{
    
    public function __invoke(Request $request, $params)
    {
        $repo = $this->em->getRepository(Entity\Package::class);
        $package = $repo->findOneBy(['id'=>$params['package'], 'account'=>$this->accountUser]);
        if(!$package) {
            $request->getSession()->getFlashBag()->add('alert', "There was a problem accessing this package");
            return new RedirectResponse($this->router->generate('profile'));
        }
       
        $form = $this->forms->create('package', $package);
        $form->handleRequest();

        if ($form->isValid()) {
            $this->em->persist($package);
            $this->em->flush();
            $request->getSession()->getFlashBag()->add('success', "Your package was succesfully updated");

        }

        return new Response($this->renderer->render("submit.html", ['form'=>$form->createView()]));


    }
}