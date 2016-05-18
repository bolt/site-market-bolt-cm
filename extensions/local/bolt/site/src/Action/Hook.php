<?php

namespace Bolt\Extension\Bolt\MarketPlace\Action;

use Bolt\Extension\Bolt\MarketPlace\Entity;
use Bolt\Extension\Bolt\MarketPlace\Repository\Package;
use Bolt\Extension\Bolt\MarketPlace\Service\PackageManager;
use Bolt\Storage\EntityManager;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;

class Hook extends AbstractAction
{
    /**
     * {@inheritdoc}
     */
    public function execute(Request $request, array $params)
    {
        $token = $request->get('token');
        /** @var EntityManager $em */
        $em = $this->getAppService('storage');
        /** @var Package $repo */
        $repo = $em->getRepository(Entity\Package::class);

        $package = $repo->findOneBy(['token' => $token]);
        if ($package) {
            $services = $this->getAppService('marketplace.services');
            /** @var PackageManager $packageManager */
            $packageManager = $services['package_manager'];
            $package = $packageManager->syncPackage($package);
            $response = ['status' => 'ok', 'response' => $package];
        } else {
            $response = ['status' => 'error', 'response' => 'package not found'];
        }

        return new JsonResponse($response);
    }
}
