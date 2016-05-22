<?php

namespace Bolt\Extension\Bolt\MarketPlace\Action;

use Bolt\Extension\Bolt\MarketPlace\Storage\Entity;
use Bolt\Extension\Bolt\MarketPlace\Storage\Repository;
use Bolt\Extension\Bolt\Members\Storage\Entity\Account;
use Bolt\Extension\Bolt\Members\Storage\Records;
use Bolt\Storage\EntityManager;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Session\Session;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

class PackageStar extends AbstractAction
{
    /**
     * {@inheritdoc}
     */
    public function execute(Request $request, array $params)
    {
        $packageId = $params['package'];

        /** @var Session $session */
        $session = $this->getAppService('session');
        /** @var EntityManager $em */
        $em = $this->getAppService('storage');

        /** @var Repository\Package $packageRepo */
        $packageRepo = $em->getRepository(Entity\Package::class);
        /** @var Entity\Package $package */
        $package = $packageRepo->findOneBy(['id' => $packageId]);

        /** @var Records $records */
        $records = $this->getAppService('members.records');
        /** @var Account $account */
        $account = $records->getAccountByGuid($package->getAccountId());

        /** @var Repository\Stat $statRepo */
        $statRepo = $em->getRepository(Entity\Stat::class);
        /** @var Entity\Stat $existing */
        $existing = $statRepo->findOneBy(['package_id' => $package, 'account_id' => $account->getGuid()]);

        if ($existing) {
            $session->getFlashBag()->add('error', 'Your have already starred this package');
        } else {
            $stat = new Entity\Stat([
                'source'     => $request->server->get('HTTP_REFERER'),
                'ip'         => $request->server->get('REMOTE_ADDR'),
                'recorded'   => new \DateTime(),
                'package_id' => $package,
                'type'       => 'star',
                'account_id' => $account->getGuid(),
            ]);

            $statRepo->save($stat);
            $session->getFlashBag()->add('success', 'Your have starred this package');
        }

        /** @var UrlGeneratorInterface $urlGen */
        $urlGen = $this->getAppService('url_generator');
        $route = $urlGen->generate('view', ['package' => $packageId]);

        return new RedirectResponse($route);
    }
}
