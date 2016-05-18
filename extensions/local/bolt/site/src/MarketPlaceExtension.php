<?php

namespace Bolt\Extension\Bolt\MarketPlace;

use Bolt\Extension\SimpleExtension;
use Silex\Application;

/**
 * Extension site extension loader
 *
 * @author Gawain Lynch <gawain.lynch@gmail.com>
 */
class MarketPlaceExtension extends SimpleExtension
{
    /**
     * {@inheritdoc}
     */
    public function getServiceProviders()
    {
        $providers = parent::getServiceProviders();
        $providers[] = new Provider\MarketPlaceServiceProvider();

        return $providers;
    }

    /**
     * {@inheritdoc}
     */
    protected function registerFrontendControllers()
    {
        $app = $this->getContainer();

        return [
            '/' => $app['marketplace.controller.frontend'],
        ];
    }
}
