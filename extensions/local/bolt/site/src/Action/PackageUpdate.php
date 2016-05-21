<?php

namespace Bolt\Extension\Bolt\MarketPlace\Action;

use Bolt\Extension\Bolt\MarketPlace\Storage\Entity;
use Bolt\Extension\Bolt\MarketPlace\Service\PackageManager;
use Bolt\Storage\EntityManager;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Session\Session;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

class PackageUpdate extends AbstractAction
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
        $services = $this->getAppService('marketplace.services');
        /** @var PackageManager $packageManager */
        $packageManager = $services['package_manager'];
        /** @var EntityManager $em */
        $em = $this->getAppService('storage');
        $repo = $em->getRepository(Entity\Package::class);

        $package = $repo->findOneBy(['id' => $params['package']]);
        if ($package->account->admin) {
            $isAdmin = true;
        } else {
            $isAdmin = false;
        }
        try {
            $packageManager->validate($package, $isAdmin);
            $package = $packageManager->syncPackage($package);
            $session->getFlashBag()->add('success', 'Package ' . $package->name . ' has been updated');
            if ($package->account->approved) {
                $package->approved = true;
            }
            if (!$package->token) {
                $package->regenerateToken();
            }
        } catch (\Exception $e) {
            $session->getFlashBag()->add('error', 'Package has an invalid composer.json and will be disabled!');
            $session->getFlashBag()->add('warning', implode(' : ', [$e->getMessage(), $e->getFile(), $e->getLine()]));
            $package->approved = false;
        }

//@TODO update
//$this->em->flush();

        $route = $urlGen->generate('profile');

        return new RedirectResponse($route);
    }
}
