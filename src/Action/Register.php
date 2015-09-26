<?php

namespace Bolt\Extensions\Action;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Twig_Environment;
use Doctrine\ORM\EntityManager;
use Symfony\Component\Form\FormFactory;
use Aura\Router\Router;
use Bolt\Extensions\Entity;

class Register
{
    public $renderer;
    public $em;
    public $forms;
    public $router;

    public function __construct(Twig_Environment $renderer, EntityManager $em, FormFactory $forms, Router $router)
    {
        $this->renderer = $renderer;
        $this->em = $em;
        $this->forms = $forms;
        $this->router = $router;
    }

    public function __invoke(Request $request)
    {
        $entity = new Entity\Account();
        $form = $this->forms->create('account', $entity);

        $form->handleRequest();

        if ($form->isValid()) {
            $account = $form->getData();

            $repo = $this->em->getRepository(Entity\Account::class);
            $existing = $repo->findOneBy(['username' => $account->username]);
            if ($existing) {
                $request->getSession()
                        ->getFlashBag()
                        ->add('error', 'The username '.$account->username.' is already in use. Please try again with a different username');
            } else {
                $account->created = new \DateTime();
                $account->approved = true;
                $account->regenerateToken();

                $this->em->persist($account);
                $this->em->flush();
                $request->getSession()
                        ->getFlashBag()
                        ->add('success', 'Your account has been created, you can now login.');
                $request->getSession()->save();

                return new RedirectResponse($this->router->generate('login'));
            }
        }

        return new Response($this->renderer->render('register.html', ['form' => $form->createView()]));
    }
}
