<?php

namespace Bolt\Extension\Bolt\MarketPlace\Action;

use Bolt\Extension\Bolt\MarketPlace\Service\PackageManager;
use Bolt\Extension\Bolt\MarketPlace\Storage\Entity;
use Bolt\Extension\Bolt\MarketPlace\Storage\Repository;
use Bolt\Extension\Bolt\Members\Storage\Entity\Account;
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
        $route = $urlGen->generate('profile');

        /** @var Session $session */
        $session = $this->getAppService('session');
        $services = $this->getAppService('marketplace.services');
        /** @var PackageManager $packageManager */
        $packageManager = $services['package_manager'];
        /** @var EntityManager $em */
        $em = $this->getAppService('storage');
        /** @var Repository\Package $packageRepo */
        $packageRepo = $em->getRepository(Entity\Package::class);

        /** @var Entity\Package $package */
        $package = $packageRepo->findOneBy(['id' => $params['package']]);
        if (!$package) {
            $session->getFlashBag()->add('error', 'Package account is invalid!');

            return new RedirectResponse($route);
        }

        $membersRecords = $this->getAppService('members.records');
        /** @var Account $account */
        $account = $membersRecords->getAccountByGuid($package->getAccountId());
        $roles = (array) $account->getRoles();
        if (in_array('admin', $roles)) {
            $isAdmin = true;
        } else {
            $isAdmin = false;
        }
        try {
            $packageManager->validate($package, $isAdmin);
            $package = $packageManager->syncPackage($package);
            $session->getFlashBag()->add('success', 'Package ' . $package->getName() . ' has been updated');

            if ($account->isEnabled()) {
                $package->setApproved(true);
            }
            if (!$package->getToken()) {
                $package->regenerateToken();
            }
        } catch (\Exception $e) {
            $session->getFlashBag()->add('error', 'Package has an invalid composer.json and will be disabled!');
            $session->getFlashBag()->add('warning', implode(' : ', [$e->getMessage(), $e->getFile(), $e->getLine()]));
            $package->setApproved(false);
        }

        $packageRepo->save($package);

        return new RedirectResponse($route);
    }
}
