<?php

namespace Bolt\Extension\Bolt\MarketPlace\Action;

use Bolt\Extension\Bolt\MarketPlace\Service\SatisManager;
use Bolt\Extension\Bolt\MarketPlace\Storage\Entity;
use Bolt\Extension\Bolt\MarketPlace\Storage\Repository;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Repository web hook callback action.
 *
 * @author Gawain Lynch <gawain.lynch@gmail.com>
 * @author Ross Riley <riley.ross@gmail.com>
 */
class WebhookListener extends AbstractAction
{
    /**
     * {@inheritdoc}
     */
    public function execute(Request $request, array $params)
    {
        if ($request->query->get('token') === null) {
            return new JsonResponse(['status' => 'error', 'response' => 'Invalid request'], Response::HTTP_BAD_REQUEST);
        }
        
        $services = $this->getAppService('marketplace.services');
        /** @var SatisManager $satisProvider */
        $satisProvider = $services['satis_manager'];
        $response = $satisProvider->queueWebhook($request);

        return $response;
    }
}
