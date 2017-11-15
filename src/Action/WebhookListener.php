<?php

namespace Bundle\Site\MarketPlace\Action;

use Bundle\Site\MarketPlace\Service\Queue\QueueManager;
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

        /** @var QueueManager $queueManager */
        $queueManager = $this->getAppService('marketplace.manager_queue');
        $response = $queueManager->queueWebhook($request);

        return $response;
    }
}
