<?php

namespace Bolt\Extension\Bolt\MarketPlace\Action;

use Bolt\Extension\Bolt\MarketPlace\Entity;
use Bolt\Extension\Bolt\MarketPlace\Repository;
use Bolt\Storage\EntityManager;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;

class JsonSearch extends AbstractAction
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
        return [
            'id'          => $package->getId(),
            'title'       => $package->getTitle(),
            'source'      => $package->getSource(),
            'name'        => $package->getName(),
            'keywords'    => $package->getKeywords(),
            'type'        => $package->getType(),
            'description' => $package->getDescription(),
            //'documentation' => $package->getDocumentation(),
            'approved'     => $package->getApproved(),
            'requirements' => $package->getRequirements(),
            'versions'     => $package->getVersions(),
            'created'      => $package->getCreated(),
            'updated'      => $package->getUpdated(),
            'authors'      => $package->getAuthors(),
//@TODO 'user' key needs to be fixed
            'user'         => [
                'id'         => $package->getAccount()->getId(),
                'username'   => $package->getAccount()->getUsername(),
                'name'       => $package->getAccount()->getName(),
                'email_hash' => [
                    'type' => 'md5',
                    'hash' => md5($package->getAccount()->getEmail()),
                ],
            ],
            //'token' => $package->getToken(),
            //'stats' => $package->getStats(),
            //'builds' => $package->getBuilds(),
            'screenshots' => $package->getScreenshots(),
            'icon'        => $package->getIcon(),
            'support'     => $package->getSupport(),
        ];
    }
}
