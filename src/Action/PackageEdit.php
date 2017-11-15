<?php

namespace Bundle\Site\MarketPlace\Action;

use Bundle\Site\MarketPlace\Storage\Entity;
use Bundle\Site\MarketPlace\Storage\Repository;
use Bolt\Storage\EntityManager;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\Form\FormFactory;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Session\Session;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

/**
 * Edit package action.
 *
 * @author Gawain Lynch <gawain.lynch@gmail.com>
 * @author Ross Riley <riley.ross@gmail.com>
 */
class PackageEdit extends AbstractAction
{
    /**
     * {@inheritdoc}
     */
    public function execute(Request $request, array $params)
    {
        /** @var UrlGeneratorInterface $urlGen */
        $urlGen = $this->getAppService('url_generator');
        /** @var Session $session */
        $session = $this->getAppService('session');
        /** @var EntityManager $em */
        $em = $this->getAppService('storage');
        $repo = $em->getRepository(Entity\Package::class);
        /** @var Entity\Package $package */
        $package = $repo->findOneBy(['id' => $params['package'], 'account_id' => $params['user']->getGuid()]);
        if (!$package) {
            $session->getFlashBag()->add('error', 'There was a problem accessing this package');
            $route = $urlGen->generate('profile');

            return new RedirectResponse($route);
        }

        /** @var Repository\PackageToken $tokenRepo */
        $tokenRepo = $em->getRepository(Entity\PackageToken::class);
        $tokenRepo->getValidPackageToken($package->getId(), 'webhook');

        $formsService = $this->getAppService('marketplace.forms');
        /** @var FormFactory $forms */
        $forms = $this->getAppService('form.factory');
        /** @var FormInterface $form */
        $form = $forms
            ->createBuilder($formsService['package'], $package)
            ->addEventListener(FormEvents::POST_SUBMIT,
                function (FormEvent $event) {
                    $event->stopPropagation();
                }, 128
            )
            ->getForm()
        ;
        $form->handleRequest($request);

        if ($form->isValid()) {
            $repo->save($package);

            $session->getFlashBag()->add('success', 'Your package was successfully updated');
        }

        /** @var \Twig_Environment $twig */
        $twig = $this->getAppService('twig');
        $context = [
            'form'     => $form->createView(),
            'package'  => $package,
            'webhook'  => $this->getWebhookData($package),
        ];
        $html = $twig->render('submit.twig', $context);

        return new Response($html);
    }
}
