<?php
/**
 * An action class to handle a password reset request
 *
 *
 * @author Ross Riley, riley.ross@gmail.com
 */

namespace Bolt\Extensions\Action;

use Bolt\Extensions\Entity;
use Bolt\Extensions\Service\MailService;
use DateInterval;
use DateTime;
use Doctrine\ORM\EntityManager;
use RandomLib\Factory;
use RandomLib\Generator;
use SecurityLib\Strength;
use Symfony\Component\Form\FormFactory;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Twig_Environment;



class PasswordReset
{
    public $renderer;
    public $em;
    public $forms;
    public $mailservice;

    public function __construct(Twig_Environment $renderer, EntityManager $em, FormFactory $forms, MailService $mailservice)
    {
        $this->renderer = $renderer;
        $this->em = $em;
        $this->forms = $forms;
        $this->mailservice = $mailservice;
    }

    public function __invoke(Request $request)
    {
        $repo = $this->em->getRepository(Entity\Account::class);

        if ($request->get('token')) {
            $tokenAccount = $repo->findOneBy(['token' => $request->get('token')]);
            $current = new DateTime();
            $form = $this->forms->create('passwordupdate', $tokenAccount);
            $form->handleRequest();

            if ($tokenAccount && $form->isValid() && !$form->get('password')->getData()) {
                $request->getSession()
                    ->getFlashBag()
                    ->add('error', 'The passwords entered do not match, please check and try again.');
                return new Response($this->renderer->render('reset-password.html', ['tokenValid'=> true, 'form' => $form->createView()]));

            } elseif ($tokenAccount && $form->isValid()) {
                $tokenAccount = $repo->findOneBy(['token' => $request->get('token')]);
                $tokenAccount->setPassword($form->get('password')->getData());
                $tokenAccount->setToken(null);
                $tokenAccount->setTokenvalid(null);
                $this->em->persist($tokenAccount);
                $this->em->flush();

                return new Response($this->renderer->render('reset-password.html', ['resetSuccessful'=> true]));
            } elseif ($tokenAccount && ($current < $tokenAccount->tokenvalid)) {

                return new Response($this->renderer->render('reset-password.html', ['tokenValid'=> true, 'form' => $form->createView()]));
            } else {
                return new Response($this->renderer->render('reset-password.html', ['tokenValid'=> false]));
            }
        }

        $form = $this->forms->create('passwordreset');

        $form->handleRequest();

        if ($form->isValid()) {
            $account = $form->getData();


            $account = $repo->findOneBy(['email' => $account['email']]);

            $factory = new Factory;
            $generator = $factory->getGenerator(new Strength(Strength::MEDIUM));
            $token = $generator->generateString(32, Generator::CHAR_ALNUM);
            $account->token = $token;

            $expiry = new DateTime();
            $expiry->add(new DateInterval('PT10M'));
            $account->tokenvalid = $expiry;

            $this->em->persist($account);
            $this->em->flush();

            $response = $this->mailservice->sendTemplate('reset', $account->email, $account->name, ['account'=>$account]);
            if (isset($response[0]['status']) && $response[0]['status']) {
                return new Response($this->renderer->render('reset-requested.html'));
            }


        }

        return new Response($this->renderer->render('reset.html', ['form' => $form->createView()]));
    }

}