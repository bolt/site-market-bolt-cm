<?php

namespace Bolt\Extension\Bolt\MarketPlace\Action;

use Bolt\Extension\Bolt\MarketPlace\Entity;
use Bolt\Extension\Bolt\MarketPlace\Service\PackageManager;
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
        $accountUser = $request->get('user');

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
        $form = $forms->create('package', $entity);
        $form->handleRequest();

        if ($form->isValid()) {
            $package = $form->getData();
            $package->created = new \DateTime();
            $package->regenerateToken();
            $package->account = $accountUser;
            if ($accountUser->approved) {
                $package->approved = true;
            }

            try {
                $packageManager->validate($package, $accountUser->admin);
                $package = $packageManager->syncPackage($package);
                $repo->save($package);
                $route = $urlGen->generate('submitted');

                return new RedirectResponse($route);
            } catch (\Exception $e) {
                $session->getFlashBag()->add('error', 'Package has an invalid composer.json!');
                $session->getFlashBag()->add('warning', $e->getMessage());
                $package->approved = false;
                $error = 'invalid';
            }
        }

        /** @var \Twig_Environment $twig */
        $twig = $this->getAppService('twig');
        $context = ['form' => $form->createView(), 'error' => $error];
        $html = $twig->render('submit.twig', $context);

        return new Response($html);
    }
}
