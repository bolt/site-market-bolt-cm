<?php

namespace Bolt\Extension\Bolt\MarketPlace\Provider;

use Bolt\Extension\Bolt\MarketPlace\Action;
use Bolt\Extension\Bolt\MarketPlace\Controller;
use Bolt\Extension\Bolt\MarketPlace\Service;
use Bolt\Extension\Bolt\MarketPlace\Twig;
use Composer\Config as ComposerConfig;
use Composer\Config\JsonConfigSource;
use Composer\Factory;
use Composer\IO\NullIO;
use Composer\Json\JsonFile;
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

        $app['marketplace.composer.config'] = $app->share(
            function ($app) {
                /** @var \Bolt\Composer\Action\Options $options */
                $options = $app['extend.action.options'];
                putenv('COMPOSER_HOME=' . $options->baseDir());

                $io = new NullIO();

                /** @var ComposerConfig $config */
                $config = Factory::createConfig($io);

                foreach (['auth.json', 'github.json'] as $jsonFile) {
                    $jsonFilePath = $app['resources']->getPath('config/satis/' .  $jsonFile);
                    $file = new JsonFile($jsonFilePath);
                    if ($file->exists()) {
                        $config->merge(['config' => $file->read()]);
                        $config->setAuthConfigSource(new JsonConfigSource($file, true));
                    }
                }

                return $config;
            }
        );

        $app['marketplace.actions'] = $app->share(
            function ($app) {
                $container = new Container([
                    'admin'             => $app->share(function () use ($app) { return new Action\Admin($app); }),
                    'feed'              => $app->share(function () use ($app) { return new Action\Feed($app); }),
                    'home'              => $app->share(function () use ($app) { return new Action\Home($app); }),
                    'hook'              => $app->share(function () use ($app) { return new Action\Hook($app); }),
                    'json_search'       => $app->share(function () use ($app) { return new Action\JsonSearch($app); }),
                    'listing'           => $app->share(function () use ($app) { return new Action\Listing($app); }),
                    'list_packages'     => $app->share(function () use ($app) { return new Action\ListPackages($app); }),
                    'package_disable'   => $app->share(function () use ($app) { return new Action\PackageDisable($app); }),
                    'package_edit'      => $app->share(function () use ($app) { return new Action\PackageEdit($app); }),
                    'package_info'      => $app->share(function () use ($app) { return new Action\PackageInfo($app); }),
                    'package_releases'  => $app->share(function () use ($app) { return new Action\PackageReleases($app); }),
                    'package_stats'     => $app->share(function () use ($app) { return new Action\PackageStats($app); }),
                    'package_stats_api' => $app->share(function () use ($app) { return new Action\PackageStatsApiDownloads($app); }),
                    'package_star'      => $app->share(function () use ($app) { return new Action\PackageStar($app); }),
                    'package_update'    => $app->share(function () use ($app) { return new Action\PackageUpdate($app); }),
                    'package_view'      => $app->share(function () use ($app) { return new Action\PackageView($app); }),
                    'packages_author'   => $app->share(function () use ($app) { return new Action\PackagesByAuthor($app); }),
                    'ping'              => $app->share(function () use ($app) { return new Action\Ping($app); }),
                    'profile'           => $app->share(function () use ($app) { return new Action\Profile($app); }),
                    'search'            => $app->share(function () use ($app) { return new Action\Search($app); }),
                    'stat'              => $app->share(function () use ($app) { return new Action\Stat($app); }),
                    'submit'            => $app->share(function () use ($app) { return new Action\Submit($app); }),
                    'submitted'         => $app->share(function () use ($app) { return new Action\Submitted($app); }),
                    'test_build_check'  => $app->share(function () use ($app) { return new Action\TestBuildCheck($app); }),
                    'test_extension'    => $app->share(function () use ($app) { return new Action\TestExtension($app); }),
                    'tests'             => $app->share(function () use ($app) { return new Action\Tests($app); }),
                    'v3_ready'          => $app->share(function () use ($app) { return new Action\V3Ready($app); }),
                ]);

                return $container;
            }
        );

        $app['marketplace.services'] = $app->share(
            function ($app) {
                $container = new Container([
                    'bolt_themes'     => $app->share(function () use ($app) { return new Service\BoltThemes(); }),
                    'email'           => $app->share(function () use ($app) { return new Service\Email(); }),
                    'mail'            => $app->share(function () use ($app) { return new Service\MailService(); }),
                    'package_manager' => $app->share(function () use ($app) { return new Service\PackageManager($app['marketplace.composer.config']); }),
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
