<?php

namespace Bolt\Extension\Bolt\MarketPlace\Action;

use Bolt\Extension\Bolt\MarketPlace\Storage\Entity;
use Bolt\Extension\Bolt\MarketPlace\Storage\Repository;
use Bolt\Storage\EntityManager;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;

/**
 * JSON search action.
 *
 * @author Gawain Lynch <gawain.lynch@gmail.com>
 * @author Ross Riley <riley.ross@gmail.com>
 */
class SearchJson extends AbstractAction
{
    /**
     * {@inheritdoc}
     */
    public function execute(Request $request, array $params)
    {
        $search = $request->get('q');
        $type = $request->get('type') ?: null;
        $order = $request->get('order') ?: null;

        /** @var EntityManager $em */
        $em = $this->getAppService('storage');
        /** @var Repository\Package $repo */
        $repo = $em->getRepository(Entity\Package::class);
        $packages = $repo->search($search, $type, $order);

        $result = [];
        foreach ($packages as $package) {
            $result[] = $this->formatPackage($package);
        }

        return new JsonResponse($result);
    }

    /**
     * @param Entity\Package $package
     *
     * @return array
     */
    private function formatPackage(Entity\Package $package)
    {
        /** @var \Bolt\Extension\Bolt\Members\Storage\Records $membersRecords */
        $membersRecords = $this->getAppService('members.records');
        $account = $membersRecords->getAccountByGuid($package->getAccountId());
        $accountMeta = $membersRecords->getAccountMeta($package->getAccountId(), 'username');
        $updateEntities = $this->getUpdated($package);
        $versions = [];
        if ($versionEntities = $this->getVersions($package)) {
            /**
             * @var int                   $version
             * @var Entity\PackageVersion $versionEntity
             */
            foreach ($versionEntities as $version => $versionEntity) {
                $versions[$version] = $versionEntity->getPrettyVersion();
            }
        }

        return [
            'id'            => $package->getId(),
            'title'         => $package->getTitle(),
            'source'        => $package->getSource(),
            'name'          => $package->getName(),
            'keywords'      => $package->getKeywords(),
            'type'          => $package->getType(),
            'description'   => $package->getDescription(),
            'documentation' => $package->getDocumentation(),
            'approved'      => $package->isApproved(),
            'versions'      => $versions,
            'created'       => $package->getCreated(),
            'updated'       => [
                'dev'    => $updateEntities['dev'] ? $updateEntities['dev']->getUpdated() : null,
                'stable' => $updateEntities['stable'] ? $updateEntities['stable']->getUpdated() : null,
            ],
            'authors'       => $package->getAuthors(),
            'user'          => [
                'id'         => $account->getId(),
                'username'   => $accountMeta ? $accountMeta->getValue() : null,
                'name'       => $account->getDisplayname(),
                'email_hash' => [
                    'type' => 'md5',
                    'hash' => md5($account->getEmail()),
                ],
            ],
            'screenshots'   => $package->getScreenshots(),
            'icon'          => $package->getIcon(),
            'support'       => $package->getSupport(),
        ];
    }
}
