<?php
namespace Bolt\Extensions\Action;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\RedirectResponse;

use Bolt\Extensions\Entity;


class Register extends AbstractAction
{
    
    public function __invoke(Request $request)
    {
        
        $entity = new Entity\Account;
        $form = $this->forms->create('account', $entity);
        
        $form->handleRequest();

        if ($form->isValid()) {
            $account = $form->getData();

            $repo = $this->em->getRepository(Entity\Account::class);
            $existing = $repo->findOneBy(['username'=>$account->username]);
            if ($existing) {
                $request->getSession()->getFlashBag()->add('alert', 'The username '.$account->username.' is already in use. Please try again with a different username');
                return new RedirectResponse($this->router->generate('register'));

            }
            
            $account->created = new \DateTime;
            $account->approved = true;
            $account->regenerateToken();
           
            $this->em->persist($account);
            $this->em->flush();
            $request->getSession()->getFlashBag()->add('success', 'Your account has been created, you can now login.');
            return new RedirectResponse($this->router->generate('login'));

        }
        
        return new Response($this->renderer->render("register.html", ['form'=>$form->createView()]));

    }
}