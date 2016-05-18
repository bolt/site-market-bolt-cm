<?php

namespace Bolt\Extension\Bolt\MarketPlace\Provider;

use Bolt\Extension\Bolt\MarketPlace\Controller;
use Bolt\Extension\Bolt\MarketPlace\Service;
use Bolt\Extension\Bolt\MarketPlace\Twig;
use Pimple as Container;
use Silex\Application;
use Silex\ServiceProviderInterface;

/**
 * Market Place Service Provider.
 *
 * @author Gawain Lynch <gawain.lynch@gmail.com>
 */
class MarketPlaceServiceProvider implements ServiceProviderInterface
{
    /**
     * @inheritDoc
     */
    public function register(Application $app)
    {
        $app['twig'] = $app->share(
            $app->extend(
                'twig',
                function (\Twig_Environment $twig) {
                    /** @var \Twig_Environment $twig */
                    $twig->addExtension(new Twig\Extension());

                    return $twig;
                }
            )
        );

        $app['marketplace.controller.frontend'] = $app->share(
            function () {
                return new Controller\Frontend();
            }
        );

        $app['marketplace.services'] = $app->share(
            function ($app) {
                $container = new Container([
                    'bolt_themes'     => $app->share(function () use ($app) { return new Service\BoltThemes(); }),
                    'email'           => $app->share(function () use ($app) { return new Service\Email(); }),
                    'mail'            => $app->share(function () use ($app) { return new Service\MailService(); }),
                    'package_manager' => $app->share(function () use ($app) { return new Service\PackageManager(); }),
                ]);

                return $container;
            }
        );
    }

    /**
     * @inheritDoc
     */
    public function boot(Application $app)
    {
    }
}
