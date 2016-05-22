<?php

namespace Bolt\Extension\Bolt\MarketPlace;

use Bolt\Extension\Bolt\MarketPlace\Command;
use Bolt\Extension\Bolt\MarketPlace\Storage\Entity;
use Bolt\Extension\Bolt\MarketPlace\Storage\Repository;
use Bolt\Extension\Bolt\MarketPlace\Storage\Schema\Table;
use Bolt\Extension\DatabaseSchemaTrait;
use Bolt\Extension\SimpleExtension;
use Bolt\Extension\StorageTrait;
use Pimple as Container;
use Silex\Application;

/**
 * Extension site extension loader
 *
 * @author Gawain Lynch <gawain.lynch@gmail.com>
 */
class MarketPlaceExtension extends SimpleExtension
{
    use DatabaseSchemaTrait;
    use StorageTrait;

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
    protected function registerServices(Application $app)
    {
        $this->extendDatabaseSchemaServices();
        $this->extendRepositoryMapping();
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

    /**
     * {@inheritdoc}
     */
    protected function registerNutCommands(Container $container)
    {
        return [
            new Command\UpdatePackage($container),
        ];
    }

    /**
     * {@inheritdoc}
     */
    protected function registerExtensionTables()
    {
        return [
            'marketplace_package'       => Table\Package::class,
            'marketplace_stat'          => Table\Stat::class,
            'marketplace_version_build' => Table\VersionBuild::class,
        ];
    }

    /**
     * {@inheritdoc}
     */
    protected function registerRepositoryMappings()
    {
        return [
            'marketplace_package'       => [Entity\Package::class      => Repository\Package::class],
            'marketplace_stat'          => [Entity\Stat::class         => Repository\Stat::class],
            'marketplace_version_build' => [Entity\VersionBuild::class => Repository\VersionBuild::class],
        ];
    }
}
