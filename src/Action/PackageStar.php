<?php

namespace Bolt\Extension\Bolt\MarketPlace\Action;

use Bolt\Extension\Bolt\MarketPlace\Storage\Entity;
use Bolt\Extension\Bolt\MarketPlace\Storage\Repository;
use Bolt\Extension\BoltAuth\Auth\Storage\Entity\Account;
use Bolt\Extension\BoltAuth\Auth\Storage\Records;
use Bolt\Storage\EntityManager;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Session\Session;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

/**
 * Packages star action.
 *
 * @author Gawain Lynch <gawain.lynch@gmail.com>
 * @author Ross Riley <riley.ross@gmail.com>
 */
class PackageStar extends AbstractAction
{
    /**
     * {@inheritdoc}
     */
    public function execute(Request $request, array $params)
    {
        $packageId = $params['package'];

        /** @var UrlGeneratorInterface $urlGen */
        $urlGen = $this->getAppService('url_generator');
        /** @var Session $session */
        $session = $this->getAppService('session');
        /** @var EntityManager $em */
        $em = $this->getAppService('storage');

        /** @var Repository\Package $packageRepo */
        $packageRepo = $em->getRepository(Entity\Package::class);
        /** @var Entity\Package $package */
        $package = $packageRepo->findOneBy(['id' => $packageId]);
        if ($package === false) {
            $session->getFlashBag()->add('error', 'Star went supernova!');

            return new RedirectResponse($urlGen->generate('home'));
        }

        /** @var Records $records */
        $records = $this->getAppService('auth.records');
        /** @var Account $account */
        $account = $records->getAccountByGuid($package->getAccountId());

        /** @var Repository\PackageStar $starRepo */
        $starRepo = $em->getRepository(Entity\PackageStar::class);
        $existing = $starRepo->isStarredBy($package->getId(), $account->getGuid());

        if ($existing) {
            $session->getFlashBag()->add('error', 'Your have already starred this package');
        } else {
            $star = new Entity\PackageStar([
                'source'     => $request->server->get('HTTP_REFERER'),
                'ip'         => $request->server->get('REMOTE_ADDR'),
                'recorded'   => new \DateTime(),
                'package_id' => $package,
                'account_id' => $account->getGuid(),
            ]);

            $starRepo->save($star);
            $session->getFlashBag()->add('success', 'Your have starred this package');
        }

        $route = $urlGen->generate('view', ['package' => $packageId]);

        return new RedirectResponse($route);
    }
}
