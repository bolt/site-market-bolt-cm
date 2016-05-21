<?php

namespace Bolt\Extension\Bolt\MarketPlace\Action;

use Bolt\Extension\Bolt\MarketPlace\Form;
use Bolt\Extension\Bolt\MarketPlace\Storage\Entity;
use Bolt\Extension\Bolt\MarketPlace\Service\PackageManager;
use Bolt\Extension\Bolt\Members\AccessControl\Authorisation;
use Bolt\Storage\EntityManager;
use Symfony\Component\Form\FormFactory;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Session\Session;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

class Submit extends AbstractAction
{
    /**
     * {@inheritdoc}
     */
    public function execute(Request $request, array $params)
    {
        $entity = new Entity\Package();
        $error = false;
        /** @var Authorisation $accountUser */
        $accountUser = $params['user'];

        /** @var UrlGeneratorInterface $urlGen */
        $urlGen = $this->getAppService('url_generator');
        /** @var Session $session */
        $session = $this->getAppService('session');
        /** @var EntityManager $em */
        $em = $this->getAppService('storage');
        $repo = $em->getRepository(Entity\Package::class);
        $services = $this->getAppService('marketplace.services');
        /** @var PackageManager $packageManager */
        $packageManager = $services['package_manager'];
        /** @var FormFactory $forms */
        $forms = $this->getAppService('form.factory');
        $form = $forms->create(Form\PackageForm::class, $entity);
        $form->handleRequest($request);

        if ($form->isValid()) {
            $package = $form->getData();
            $package->setCreated(new \DateTime());
            $package->regenerateToken();
            $package->setAccountId($accountUser->getGuid());
            if ($accountUser->getAccount()->isEnabled()) {
                $package->setApproved(true);
            }

            $route = $urlGen->generate('submitted');
            $roles = $accountUser->getAccount()->getRoles();

            try {
                $packageManager->validate($package, in_array('admin', $roles));
                $package = $packageManager->syncPackage($package);
                $repo->save($package);

                return new RedirectResponse($route);
            } catch (\Exception $e) {
                $session->getFlashBag()->add('error', 'Package has an invalid composer.json!');
                $session->getFlashBag()->add('warning', $e->getMessage());
                $package->setApproved(false);
                $error = 'invalid';
            }
        }

        /** @var \Twig_Environment $twig */
        $twig = $this->getAppService('twig');
        $context = [
            'form'  => $form->createView(),
            'hook'  => $package && $package->getToken() ? $urlGen->generate('hook', ['token' => $package->getToken()], UrlGeneratorInterface::ABSOLUTE_URL) : false,
            'error' => $error
        ];
        $html = $twig->render('submit.twig', $context);

        return new Response($html);
    }
}
