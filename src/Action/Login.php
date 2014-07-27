<?php
namespace Bolt\Extensions\Action;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\RedirectResponse;

use Bolt\Extensions\Entity;


class Login extends AbstractAction
{
    
    public function __invoke(Request $request)
    {
        $form = $this->forms->createBuilder()
            ->add("email","text")
            ->add("password", "password")
            ->add('login', 'submit')
            ->getForm();

        $form->handleRequest();

        if($form->isValid()) {
            $repo = $this->em->getRepository(Entity\Account::class);
            $user = $repo->findOneBy(["email"=>$form->getData()["email"]]);
            if (null !== $user && password_verify($form->getData()["password"], $user->password)) {
                $request->getSession()->set("bolt.account.id", $user->id);
                $dest = ($ret = $request->getSession()->get('bolt.auth.return')) ? $ret : $this->router->generate("home");
                $request->getSession()->remove('bolt.auth.return');
                return new RedirectResponse($dest);
            } else {
                $request->getSession()->getFlashBag()->add('alert', 'Login Unsuccessful!');
            }
        }
        
        return new Response( $this->renderer->render("login.html", ['form'=>$form->createView()]) );

    }
}