<?php
namespace Bolt\Extensions\Action;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\RedirectResponse;

use Bolt\Extensions\Entity;

class Submit extends AbstractAction 
{
    
    public function __invoke(Request $request)
    {
        if (! $this->restrictAccess($request)) {
            return new RedirectResponse($this->router->generate('login'));
        }
        $error = false;

        $entity = new Entity\Package;
        $form = $this->forms->create('package', $entity);
        $form->handleRequest();

        if ($form->isValid()) {
            $package = $form->getData();
            $package->created = new \DateTime;
            
            try {
                $package->sync();
                $this->em->persist($package);
                $this->em->flush();
                return new RedirectResponse($this->router->generate('submitted')); 
            } catch (\Exception $e) {
                $error = 'invalid';
            }
            
            
        }
        return new Response($this->renderer->render("submit.html", ['form'=>$form->createView(), 'error'=>$error]));

    }
    
    

}