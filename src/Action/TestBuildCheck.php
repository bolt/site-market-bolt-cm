<?php

namespace Bundle\Site\MarketPlace\Action;

use Bundle\Site\MarketPlace\Storage\Entity;
use Bundle\Site\MarketPlace\Storage\Repository\Package;
use Bolt\Storage\EntityManager;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;

/**
 * Check test build action.
 *
 * @author Gawain Lynch <gawain.lynch@gmail.com>
 * @author Ross Riley <riley.ross@gmail.com>
 */
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
            'testStatus' => $build->getTestStatus(),
        ];

        return new JsonResponse($response);
    }
}
