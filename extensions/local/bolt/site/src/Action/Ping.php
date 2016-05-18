<?php

namespace Bolt\Extension\Bolt\MarketPlace\Action;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class Ping extends AbstractAction
{
    /**
     * {@inheritdoc}
     */
    public function execute(Request $request, array $params)
    {
        return new Response('pong', Response::HTTP_OK);
    }
}
