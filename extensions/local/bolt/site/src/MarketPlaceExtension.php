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
    protected function registerServices(Application $app)
    {
        $app['twig'] = $app->share(
            $app->extend(
                'twig',
                function (\Twig_Environment $twig) {
                    $twig->addExtension(new Twig\Extension());

                    return $twig;
                }
            )
        );

        $app['extension_site.controller.frontend'] = $app->share(
            function () {
                return new Controller\Frontend();
            }
        );
    }

    /**
     * {@inheritdoc}
     */
    protected function registerFrontendControllers()
    {
        $app = $this->getContainer();
        
        return [
            '/' => $app['extension_site.controller.frontend'],
        ];
    }
}
