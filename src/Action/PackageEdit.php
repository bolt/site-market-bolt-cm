<?php

namespace Bolt\Extension\Bolt\MarketPlace\Action;

use Bolt\Extension\Bolt\MarketPlace\Form;
use Bolt\Extension\Bolt\MarketPlace\Storage\Entity;
use Bolt\Storage\EntityManager;
use Symfony\Component\Form\FormFactory;
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

        if (!$package->getToken()) {
            $package->regenerateToken();
            $repo->save($package);
        }

        $formsService = $this->getAppService('marketplace.forms');
        /** @var FormFactory $forms */
        $forms = $this->getAppService('form.factory');
        $form = $forms->create($formsService['package'], $package);
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
