<?php

namespace Bolt\Extension\Bolt\MarketPlace\Action;

use Bolt\Extension\Bolt\MarketPlace\Service\WebhookManager;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;

/**
 * Repository web hook creation action.
 *
 * @author Gawain Lynch <gawain.lynch@gmail.com>
 */
class WebhookCreator extends AbstractAction
{
    /**
     * {@inheritdoc}
     */
    public function execute(Request $request, array $params)
    {
        /** @var WebhookManager $webhookManager */
        $webhookManager = $this->getAppService('marketplace.manager_webhook');
        $webhookManager->createWebhook($request->request->get('user'), $request->request->get('repo'), $request->request->get('token'));

        return new RedirectResponse($request->headers->get('referer'));
    }
}
