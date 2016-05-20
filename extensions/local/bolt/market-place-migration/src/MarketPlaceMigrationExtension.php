<?php

namespace Bolt\Extension\Bolt\MarketPlaceMigration;

use Bolt\Extension\Bolt\MarketPlaceMigration\Command\AccountMigrate;
use Bolt\Extension\Bolt\MarketPlaceMigration\Storage\Entity;
use Bolt\Extension\Bolt\MarketPlaceMigration\Storage\Repository;
use Bolt\Extension\Bolt\MarketPlaceMigration\Storage\Schema\Table;
use Bolt\Extension\DatabaseSchemaTrait;
use Bolt\Extension\SimpleExtension;
use Bolt\Extension\StorageTrait;
use Pimple as Container;
use Silex\Application;

/**
 * Extension site migration loader
 *
 * @author Gawain Lynch <gawain.lynch@gmail.com>
 */
class MarketPlaceMigrationExtension extends SimpleExtension
{
    use DatabaseSchemaTrait;
    use StorageTrait;

    /**
     * {@inheritdoc}
     */
    protected function registerNutCommands(Container $container)
    {
        return [
            new AccountMigrate($container),
        ];
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
    protected function registerExtensionTables()
    {
        return [
            'marketplace_account' => Table\Account::class,
        ];
    }

    /**
     * {@inheritdoc}
     */
    protected function registerRepositoryMappings()
    {
        return [
            'marketplace_account' => [Entity\Account::class => Repository\Account::class],
        ];
    }
}
