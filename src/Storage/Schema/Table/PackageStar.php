<?php

namespace Bolt\Extension\Bolt\MarketPlace\Storage\Schema\Table;

use Bolt\Storage\Database\Schema\Table\BaseTable;

/**
 * Package "stars" table.
 *
 * @author Gawain Lynch <gawain.lynch@gmail.com>
 */
class PackageStar extends BaseTable
{
    /**
     * {@inheritdoc}
     */
    protected function addColumns()
    {
        $this->table->addColumn('id',         'guid',     []);
        $this->table->addColumn('package_id', 'guid',     []);
        $this->table->addColumn('account_id', 'guid',     ['notnull' => false]);
        $this->table->addColumn('source',     'string',   ['notnull' => false]);
        $this->table->addColumn('ip',         'string',   ['notnull' => false]);
        $this->table->addColumn('recorded',   'datetime', ['notnull' => false]);
    }

    /**
     * {@inheritdoc}
     */
    protected function addIndexes()
    {
        $this->table->addIndex(['package_id']);
        $this->table->addIndex(['account_id']);
    }

    /**
     * {@inheritdoc}
     */
    protected function setPrimaryKey()
    {
        $this->table->setPrimaryKey(['id']);
    }

    /**
     * {@inheritdoc}
     */
    protected function addForeignKeyConstraints()
    {
        $this->table->addForeignKeyConstraint($this->tablePrefix . 'marketplace_package', ['package_id'], ['id'], ['onDelete' => 'CASCADE']);
        $this->table->addForeignKeyConstraint($this->tablePrefix . 'auth_account', ['account_id'], ['guid'], ['onDelete' => 'CASCADE']);
    }
}
