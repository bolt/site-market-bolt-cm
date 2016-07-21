<?php

namespace Bolt\Extension\Bolt\MarketPlace\Action;

use Bolt\Extension\Bolt\MarketPlace\Storage\Entity;
use Bolt\Extension\Bolt\MarketPlace\Storage\Repository;
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

        if (isset($params['sort'])) {
            if ($params['sort'] === 'downloaded') {
                $packages = $installRepo->getRankedPackages(200, $params['type']);
            }
        } elseif ($search = $request->get('name')) {
            $packages = $packageRepo->search($search);
        } else {
            $packages = $packageRepo->findBy(['approved' => true]);
        }
        array_walk($packages, function (&$v, $k) {
            $v = $v->serialize();
            unset($v['approved']);
            unset($v['account']);
        });

        $response = new JsonResponse(['packages' => $packages]);
        $response->setCallback($request->get('callback'));

        return $response;
    }
}
