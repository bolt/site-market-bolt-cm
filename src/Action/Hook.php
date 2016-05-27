<?php

namespace Bolt\Extension\Bolt\MarketPlace\Action;

use Bolt\Extension\Bolt\MarketPlace\Service\PackageManager;
use Bolt\Extension\Bolt\MarketPlace\Service\SatisManager;
use Bolt\Extension\Bolt\MarketPlace\Storage\Entity;
use Bolt\Extension\Bolt\MarketPlace\Storage\Repository\Package;
use Bolt\Storage\EntityManager;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;

/**
 * Repository web hook callback action.
 *
 * @author Gawain Lynch <gawain.lynch@gmail.com>
 * @author Ross Riley <riley.ross@gmail.com>
 */
class Hook extends AbstractAction
{
    /**
     * {@inheritdoc}
     */
    public function execute(Request $request, array $params)
    {
        /** @var EntityManager $em */
        $em = $this->getAppService('storage');
        /** @var Package $repo */
        $repo = $em->getRepository(Entity\Package::class);

        /** @var Entity\Package $package */
        $package = $repo->findOneBy(['token' => $request->query->get('token')]);
        if ($package) {
            $services = $this->getAppService('marketplace.services');
            /** @var PackageManager $packageManager */
            $packageManager = $services['package_manager'];
            $packageManager->syncPackage($package);

            /** @var SatisManager $satisProvider */
            $satisProvider = $services['satis_manager'];
            $satisProvider->queuePackage($package);

            return new JsonResponse(['status' => 'ok', 'response' => $package]);
        }

        return new JsonResponse(['status' => 'error', 'response' => 'package not found']);
    }
}