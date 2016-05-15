<?php

namespace Bolt\Extension\Bolt\ExtensionSite;

use Bolt\Extension\SimpleExtension;
use Silex\Application;

/**
 * Extension site extension loader
 *
 * @author Gawain Lynch <gawain.lynch@gmail.com>
 */
class ExtensionSiteExtension extends SimpleExtension
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
    }
}
