<?php

namespace Bolt\Extension\Bolt\MarketPlace\Storage\Schema\Table;

use Bolt\Storage\Database\Schema\Table\BaseTable;

/**
 * Install statistics table.
 *
 * @author Gawain Lynch <gawain.lynch@gmail.com>
 */
class StatInstall extends BaseTable
{
    /**
     * {@inheritdoc}
     */
    protected function addColumns()
    {
        $this->table->addColumn('id',         'guid',     []);
        $this->table->addColumn('package_id', 'guid',     []);
        $this->table->addColumn('ip',         'string',   ['notnull' => false]);
        $this->table->addColumn('recorded',   'datetime', ['notnull' => false]);
        $this->table->addColumn('version',    'string',   ['notnull' => false]);
    }

    /**
     * {@inheritdoc}
     */
    protected function addIndexes()
    {
        $this->table->addIndex(['package_id']);
        $this->table->addIndex(['version']);
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
