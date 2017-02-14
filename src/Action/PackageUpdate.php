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

/**
 * Package update action.
 *
 * @author Gawain Lynch <gawain.lynch@gmail.com>
 * @author Ross Riley <riley.ross@gmail.com>
 */
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

        try {
            $this->update($package);
        } catch (\Exception $e) {
            $session->getFlashBag()->add('error', 'Package has an invalid composer.json and will be disabled!');
            $session->getFlashBag()->add('warning', implode(' : ', [$e->getMessage(), $e->getFile(), $e->getLine()]));
            $package->setApproved(false);
        }
        $packageRepo->save($package);

        return new RedirectResponse($route);
    }

    /**
     * @param Entity\Package $package
     */
    protected function update(Entity\Package $package)
    {
        /** @var Session $session */
        $session = $this->getAppService('session');
        /** @var PackageManager $packageManager */
        $packageManager = $this->getAppService('marketplace.manager_package');
        $membersRecords = $this->getAppService('members.records');
        /** @var Account $account */
        $account = $membersRecords->getAccountByGuid($package->getAccountId());

        $roles = (array) $account->getRoles();
        $isAdmin = in_array('admin', $roles);

        $packageManager->validate($package, $isAdmin);
        $packageManager->syncPackage($package);
        $session->getFlashBag()->add('success', 'Package ' . $package->getName() . ' has been updated');

        if ($account->isEnabled()) {
            $package->setApproved(true);
        }

        /** @var EntityManager $em */
        $em = $this->getAppService('storage');
        /** @var Repository\PackageToken $tokenRepo */
        $tokenRepo = $em->getRepository(Entity\PackageToken::class);
        $tokenRepo->getValidPackageToken($package->getId(), 'webhook');
    }
}
