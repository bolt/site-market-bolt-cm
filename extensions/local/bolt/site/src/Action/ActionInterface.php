<?php

namespace Bolt\Extension\Bolt\MarketPlace\Action;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Action interface.
 *
 * @author Gawain Lynch <gawain.lynch@gmail.com>
 */
interface ActionInterface
{
    /**
     * Execute the action.
     *
     * @param Request $request
     * @param array   $params
     *
     * @return Response
     */
    public function execute(Request $request, array $params);
}
