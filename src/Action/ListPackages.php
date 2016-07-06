<?php

namespace Bolt\Extension\Bolt\MarketPlace\Action;

use Bolt\Extension\Bolt\MarketPlace\Storage\Entity;
use Bolt\Extension\Bolt\MarketPlace\Storage\Repository\Package;
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
        /** @var Package $repo */
        $repo = $em->getRepository(Entity\Package::class);

        if (isset($params['sort'])) {
            if ($params['sort'] === 'downloaded') {
                $packages = $repo->getMostDownloaded($params['type'], 200);
            }
        } elseif ($search = $request->get('name')) {
            $packages = $repo->search($search);
        } else {
            $packages = $repo->findBy(['approved' => true]);
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
