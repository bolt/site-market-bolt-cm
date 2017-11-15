<?php

namespace Bundle\Site\MarketPlace\Action;

use Bundle\Site\MarketPlace\Storage\Entity;
use Bundle\Site\MarketPlace\Storage\Repository;
use Bolt\Storage\EntityManager;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;

/**
 * List packages action.
 *
 * @author Gawain Lynch <gawain.lynch@gmail.com>
 * @author Ross Riley <riley.ross@gmail.com>
 */
class ListPackages extends AbstractAction
{
    /**
     * {@inheritdoc}
     */
    public function execute(Request $request, array $params)
    {
        /** @var EntityManager $em */
        $em = $this->getAppService('storage');
        /** @var Repository\Package $packageRepo */
        $packageRepo = $em->getRepository(Entity\Package::class);
        /** @var Repository\StatInstall $installRepo */
        $installRepo = $em->getRepository(Entity\StatInstall::class);
        $boltVersion = $request->query->get('bolt');

        if (isset($params['sort'])) {
            if ($params['sort'] === 'downloaded') {
                $packages = $installRepo->getRankedPackages(200, $params['type']);
            }
        } elseif ($search = $request->get('name')) {
            $packages = $packageRepo->search($search);
        } else {
            $packages = $packageRepo->findBy(['approved' => true]);
        }
        array_walk($packages, function (Entity\Package $v) {
            unset($v['approved']);
            unset($v['account_id']);
        });
        if ($boltVersion) {
            /** @var Repository\PackageVersion $versionRepo */
            $versionRepo = $em->getRepository(Entity\PackageVersion::class);
            foreach ($packages as $key => $package) {
                $version = $versionRepo->getLatestCompatibleVersion($package->getId(), 'dev', $boltVersion);
                if ($version === false) {
                    unset($packages[$key]);
                }
            }
        }
        $final = [];
        foreach ($packages as $package) {
            $final[] = $package;
        }

        $response = new JsonResponse(['packages' => $final]);
        $response->setCallback($request->get('callback'));

        return $response;
    }
}
