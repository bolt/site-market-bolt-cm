<?php

namespace Bolt\Extension\Bolt\MarketPlace\Storage\Schema\Table;

use Bolt\Storage\Database\Schema\Table\BaseTable;

/**
 * Package token table.
 *
 * @author Gawain Lynch <gawain.lynch@gmail.com>
 */
class PackageToken extends BaseTable
{
    /**
     * {@inheritdoc}
     */
    protected function addColumns()
    {
        $this->table->addColumn('id',         'guid',   []);
        $this->table->addColumn('package_id', 'guid',   []);
        $this->table->addColumn('token',      'string', []);
        $this->table->addColumn('type',       'string', []);
    }

    /**
     * {@inheritdoc}
     */
    protected function addIndexes()
    {
        $this->table->addUniqueIndex(['token']);

        $this->table->addIndex(['type']);
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
        $this->table->addForeignKeyConstraint($this->tablePrefix . 'market_package', ['package_id'], ['id'], ['onDelete' => 'CASCADE']);
    }
}
