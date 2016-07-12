<?php

namespace Bolt\Extension\Bolt\MarketPlace\Provider;

use Bolt\Extension\Bolt\MarketPlace\Action;
use Bolt\Extension\Bolt\MarketPlace\Controller;
use Bolt\Extension\Bolt\MarketPlace\Form;
use Bolt\Extension\Bolt\MarketPlace\Form\Validator\Constraint;
use Bolt\Extension\Bolt\MarketPlace\Service;
use Bolt\Extension\Bolt\MarketPlace\Storage\Entity;
use Bolt\Extension\Bolt\MarketPlace\Storage\Repository;
use Bolt\Extension\Bolt\MarketPlace\Twig;
use Composer\Config as ComposerConfig;
use Composer\Config\JsonConfigSource;
use Composer\Factory;
use Composer\IO\BufferIO;
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
                function (\Twig_Environment $twig) use ($app) {
                    /** @var \Twig_Environment $twig */
                    $twig->addExtension(new Twig\Extension($app['marketplace.services']));

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
                /** @var ComposerConfig $config */
                $config = Factory::createConfig(new BufferIO());

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
                    'account_profile'   => $app->share(function () use ($app) { return new Action\AccountProfile($app); }),
                    'account_starred'   => $app->share(function () use ($app) { return new Action\AccountStarred($app); }),
                    'admin'             => $app->share(function () use ($app) { return new Action\Admin($app); }),
                    'feed'              => $app->share(function () use ($app) { return new Action\Feed($app); }),
                    'home'              => $app->share(function () use ($app) { return new Action\Home($app); }),
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
                    'search'            => $app->share(function () use ($app) { return new Action\Search($app); }),
                    'search_json'       => $app->share(function () use ($app) { return new Action\SearchJson($app); }),
                    'stat'              => $app->share(function () use ($app) { return new Action\Stat($app); }),
                    'status'            => $app->share(function () use ($app) { return new Action\Status($app); }),
                    'submit'            => $app->share(function () use ($app) { return new Action\Submit($app); }),
                    'test_build_check'  => $app->share(function () use ($app) { return new Action\TestBuildCheck($app); }),
                    'test_extension'    => $app->share(function () use ($app) { return new Action\TestExtension($app); }),
                    'test_listing'      => $app->share(function () use ($app) { return new Action\TestListing($app); }),
                    'v3_ready'          => $app->share(function () use ($app) { return new Action\V3Ready($app); }),
                    'webhook_creator'   => $app->share(function () use ($app) { return new Action\WebhookCreator($app); }),
                    'webhook_listener'  => $app->share(function () use ($app) { return new Action\WebhookListener($app); }),
                ]);

                return $container;
            }
        );

        $app['marketplace.queues'] = $app->share(
            function ($app) {
                $container = new Container([
                    'package' => $app->share(function () use ($app) { return new Service\Queue\PackageQueue($app['storage'], $app['resources']); }),
                    'webhook' => $app->share(function () use ($app) { return new Service\Queue\WebhookQueue($app['storage'], $app['resources']); }),
                ]);

                return $container;
            }
        );

        $app['marketplace.services'] = $app->share(
            function ($app) {
                $container = new Container([
                    'bolt_themes'     => $app->share(function () use ($app) { return new Service\BoltThemes(); }),
                    'package_manager' => $app->share(function () use ($app) { return new Service\PackageManager($app['marketplace.composer.config']); }),
                    'record_manager'  => $app->share(function () use ($app) { return new Service\RecordManager($app['storage']); }),
                    'queue_manager'   => $app->share(function () use ($app) { return new Service\Queue\QueueManager($app['storage'], $app['resources'], $app['marketplace.queues']); }),
                    'satis_manager'   => $app->share(function () use ($app) { return new Service\SatisManager($app['storage'], $app['resources']); }),
                    'statistics'      => $app->share(function () use ($app) {
                        /** @var Repository\Stat $repo */
                        $repo = $app['storage']->getRepository(Entity\Stat::class);

                        return new Service\Statistics($repo);
                    }),
                    'webhook_manager' => $app->share(function () use ($app) { return new Service\WebhookManager($app); }),
                ]);

                return $container;
            }
        );

        $app['marketplace.forms.constraints'] = $app->share(
            function ($app) {
                /** @var Repository\Package $packageRepository */
                $packageRepository = $app['storage']->getRepository(Entity\Package::class);
                $container = new Container([
                    'unique_source_url' => $app->share(function () use ($app, $packageRepository) { return new Constraint\UniqueSourceUrl($packageRepository); }),
                ]);

                return $container;
            }
        );

        $app['marketplace.forms'] = $app->share(
            function ($app) {
                $container = new Container([
                    'package' => $app->share(function () use ($app) { return new Form\PackageForm($app['marketplace.forms.constraints']['unique_source_url']); }),
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
