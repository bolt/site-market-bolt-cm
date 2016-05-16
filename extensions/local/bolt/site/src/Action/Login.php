<?php
namespace Bolt\Extension\Bolt\MarketPlace\Action;

use Aura\Router\Router;
use Bolt\Extension\Bolt\MarketPlace\Entity;
use Doctrine\ORM\EntityManager;
use Symfony\Component\Form\FormFactory;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Twig_Environment;

class Login
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
        $form = $this->forms->createBuilder()
            ->add('email', 'text')
            ->add('password', 'password')
            ->add('login', 'submit')
            ->getForm();

        $form->handleRequest();

        if ($form->isValid()) {
            $repo = $this->em->getRepository(Entity\Account::class);
            $user = $repo->findOneBy(['email' => $form->getData()['email']]);
            if (null !== $user && password_verify($form->getData()['password'], $user->password)) {
                // Sessions not persisting without saving…
                $request->getSession()->save();
                $request->getSession()->set('bolt.account.id', $user->id);
                $dest = ($ret = $request->getSession()->get('bolt.auth.return')) ? $ret : $this->router->generate('home');
                $request->getSession()->remove('bolt.auth.return');

                return new RedirectResponse($dest);
            } else {
                $request->getSession()->getFlashBag()->add('error', 'Login Unsuccessful!');
            }
        }
        
        return new Response($this->renderer->render('login.twig', ['form' => $form->createView()]));
    }
}
