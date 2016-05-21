<?php

namespace Bolt\Extension\Bolt\MarketPlace\Action;

use Bolt\Extension\Bolt\MarketPlace\Storage\Entity;
use Bolt\Storage\EntityManager;
use Symfony\Component\Form\FormFactory;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Session\Session;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Twig_Environment;

class EditPackage extends AbstractAction
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
        $package = $repo->findOneBy(['id' => $params['package'], 'account' => $request->get('user')]);
//@TODO Check this
        if (!$package->getToken()) {
            $package->regenerateToken();
            //$this->em->flush();
        }

        if (!$package) {
            $session->getFlashBag()->add('error', 'There was a problem accessing this package');
            $route = $urlGen->generate('profile');

            return new RedirectResponse($route);
        }

        /** @var FormFactory $forms */
        $forms = $this->getAppService('form.factory');
        $form = $forms->create('package', $package);
        $form->handleRequest();

        if ($form->isValid()) {
            $repo->save($package);

            $session->getFlashBag()->add('success', 'Your package was successfully updated');
        }

        /** @var \Twig_Environment $twig */
        $twig = $this->getAppService('twig');
        $context = [
            'form'    => $form->createView(),
            'hook'    => ($package->token) ? 'https://' . $request->server->get('HTTP_HOST') . $urlGen->generate('hook') . '?token=' . $package->token : false,
            'package' => $package,
        ];
        $html = $twig->render('submit.twig', $context);

        return new Response($html);
    }
}
