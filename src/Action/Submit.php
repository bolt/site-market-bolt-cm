<?php

namespace Bolt\Extension\Bolt\MarketPlace\Action;

use Bolt\Extension\Bolt\MarketPlace\Service\PackageManager;
use Bolt\Extension\Bolt\MarketPlace\Storage\Entity;
use Bolt\Extension\Bolt\MarketPlace\Storage\Repository;
use Bolt\Extension\BoltAuth\Auth\AccessControl\Authorisation;
use Bolt\Storage\EntityManager;
use Symfony\Component\Form\FormFactory;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Session\Session;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

/**
 * Submit package action.
 *
 * @author Gawain Lynch <gawain.lynch@gmail.com>
 * @author Ross Riley <riley.ross@gmail.com>
 */
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
        /** @var Repository\Package $repo */
        $repo = $em->getRepository(Entity\Package::class);
        /** @var PackageManager $packageManager */
        $packageManager = $this->getAppService('marketplace.manager_package');
        $formsService = $this->getAppService('marketplace.forms');
        /** @var FormFactory $forms */
        $forms = $this->getAppService('form.factory');
        $form = $forms->create($formsService['package'], $entity);
        $form->handleRequest($request);

        if ($form->isValid()) {
            $package = $form->getData();
            $package->setCreated(new \DateTime());
            $package->regenerateToken();
            $package->setAccountId($accountUser->getGuid());
            if ($accountUser->getAccount()->isEnabled()) {
                $package->setApproved(true);
            }

            try {
                $roles = $accountUser->getAccount()->getRoles();
                $packageManager->validate($package, in_array('admin', $roles));
                $packageManager->syncPackage($package);
                $repo->save($package);

                $session->getFlashBag()->add('info', 'Thanks for submitting an Extension!');
                $session->getFlashBag()->add('info', 'It is now being processed and will shortly be available for searching…');

                $parts = explode('/', $package->getName());
                $route = $urlGen->generate('viewPackage', [
                    'packageAuthor' => $parts[0],
                    'packageName'   => $parts[1],
                ]);

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
            'form'    => $form->createView(),
            'error'   => $error,
            'webhook' => false,
        ];
        $html = $twig->render('submit.twig', $context);

        return new Response($html);
    }
}
