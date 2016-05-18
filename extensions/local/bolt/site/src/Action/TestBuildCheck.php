<?php

namespace Bolt\Extension\Bolt\MarketPlace\Action;

use Bolt\Extension\Bolt\MarketPlace\Entity;
use Bolt\Extension\Bolt\MarketPlace\Repository\Package;
use Bolt\Storage\EntityManager;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;

class TestBuildCheck extends AbstractAction
{
    /**
     * {@inheritdoc}
     */
    public function execute(Request $request, array $params)
    {
        /** @var EntityManager $em */
        $em = $this->getAppService('storage');
        /** @var Package $repo */
        $repo = $em->getRepository(Entity\VersionBuild::class);

        $build = $repo->findOneBy(['id' => $params['build']]);
        $response = [
            'status'     => $build->getStatus(),
            'url'        => $build->getUrl(),
            'testStatus' => $build->getTestStatus()
        ];

        return new JsonResponse($response);
    }
}
