<?php

namespace Bolt\Extension\Bolt\MarketPlace\Storage\Schema\Table;

use Bolt\Storage\Database\Schema\Table\BaseTable;

/**
 * Package version table.
 *
 * @author Gawain Lynch <gawain.lynch@gmail.com>
 */
class PackageVersion extends BaseTable
{
    /**
     * {@inheritdoc}
     */
    protected function addColumns()
    {
        $this->table->addColumn('id',             'guid',     []);
        $this->table->addColumn('package_id',     'guid',     []);
        $this->table->addColumn('version',        'string',   ['notnull' => false]);
        $this->table->addColumn('pretty_version', 'string',   ['notnull' => false]);
        $this->table->addColumn('stability',      'string',   ['notnull' => false]);
        $this->table->addColumn('updated',        'datetime', ['notnull' => false]);
        $this->table->addColumn('bolt_min',       'string',   ['notnull' => false]);
        $this->table->addColumn('bolt_max',       'string',   ['notnull' => false]);
    }

    /**
     * {@inheritdoc}
     */
    protected function addIndexes()
    {
        $this->table->addIndex(['package_id']);
        $this->table->addIndex(['version']);
        $this->table->addIndex(['stability']);
        $this->table->addIndex(['bolt_min']);
        $this->table->addIndex(['bolt_max']);
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
