<?php

namespace Bolt\Extension\Bolt\MarketPlace\Action;

use Bolt\Extension\Bolt\MarketPlace\Service\PackageManager;
use Bolt\Extension\Bolt\MarketPlace\Storage\Entity;
use Bolt\Extension\Bolt\MarketPlace\Storage\Repository;
use Bolt\Storage\EntityManager;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;

/**
 * Package information action.
 *
 * @author Gawain Lynch <gawain.lynch@gmail.com>
 * @author Ross Riley <riley.ross@gmail.com>
 */
class PackageInfo extends AbstractAction
{
    /**
     * {@inheritdoc}
     */
    public function execute(Request $request, array $params)
    {
        $p = $request->get('package');
        $bolt = $request->get('bolt');

        /** @var EntityManager $em */
        $em = $this->getAppService('storage');
        /** @var Repository\Package $packageRepo */
        $packageRepo = $em->getRepository(Entity\Package::class);
        /** @var Entity\Package $package */
        $package = $packageRepo->findOneBy(['approved' => true, 'name' => $p]);

        if (!$package) {
            return new JsonResponse(['package' => false, 'version' => false]);
        }

        /** @var PackageManager $packageManager */
        $packageManager = $this->getAppService('marketplace.manager_package');
        $allVersions = $packageManager->getInfo($package, $bolt);

        $buildRepo = $em->getRepository(Entity\VersionBuild::class);
        foreach ($allVersions as &$version) {
            /** @var Entity\VersionBuild $build */
            $build = $buildRepo->findOneBy(['package_id' => $package->getId(), 'version' => $version['version']]);
            if ($build) {
                $version['buildStatus'] = $build->getTestStatus();
            } else {
                $version['buildStatus'] = 'untested';
            }
        }

        $response = new JsonResponse(['package' => $package->serialize(), 'version' => $allVersions]);
        $response->setCallback($request->get('callback'));

        return $response;
    }
}
