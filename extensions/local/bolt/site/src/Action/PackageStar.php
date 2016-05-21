<?php

namespace Bolt\Extension\Bolt\MarketPlace\Action;

use Bolt\Extension\Bolt\MarketPlace\Storage\Entity;
use Bolt\Extension\Bolt\MarketPlace\Storage\Repository\Package;
use Bolt\Storage\EntityManager;
use Bolt\Storage\Repository;
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
        $package = $params['package'];

        /** @var UrlGeneratorInterface $urlGen */
        $urlGen = $this->getAppService('url_generator');
        $route = $urlGen->generate('view', ['package' => $package->id]);
        /** @var Session $session */
        $session = $this->getAppService('session');
        /** @var EntityManager $em */
        $em = $this->getAppService('storage');
        /** @var Package $repo */
        $repo = $em->getRepository(Entity\Package::class);
        $package = $repo->findOneBy(['id' => $package]);

        $account = $em->find(Entity\Account::class, $request->getSession()->get('bolt.account.id'));

        /** @var Repository $statRepo */
        $statRepo = $em->getRepository(Entity\Stat::class);
        $existing = $statRepo->findOneBy(['package' => $package, 'account' => $account]);

        if ($existing) {
            $session->getFlashBag()->add('error', 'Your have already starred this package');
        } else {
            $stat = new Entity\Stat([
                'source'   => $request->server->get('HTTP_REFERER'),
                'ip'       => $request->server->get('REMOTE_ADDR'),
                'recorded' => new \DateTime(),
                'package'  => $package,
                'type'     => 'star',
                'account'  => $account,
            ]);

            $statRepo->save($stat);
            $session->getFlashBag()->add('success', 'Your have starred this package');
        }

        return new RedirectResponse($route);
    }
}
