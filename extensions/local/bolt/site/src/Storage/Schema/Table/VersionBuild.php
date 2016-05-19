<?php

namespace Bolt\Extension\Bolt\MarketPlace\Storage\Schema\Table;

use Bolt\Storage\Database\Schema\Table\BaseTable;

/**
 * VersionBuild table.
 *
 * @author Gawain Lynch <gawain.lynch@gmail.com>
 */
class VersionBuild extends BaseTable
{
    /**
     * {@inheritdoc}
     */
    protected function addColumns()
    {
        $this->table->addColumn('id',         'guid',     []);
        $this->table->addColumn('package_id', 'guid',     []);
        $this->table->addColumn('version',    'string',   ['notnull' => false]);
        $this->table->addColumn('status',     'string',   ['notnull' => false]);
        $this->table->addColumn('lastrun',    'datetime', ['notnull' => false]);
        $this->table->addColumn('url',        'string',   ['notnull' => false]);
        $this->table->addColumn('hash',       'string',   ['notnull' => false]);
        $this->table->addColumn('testResult', 'text',     ['notnull' => false]);
        $this->table->addColumn('testStatus', 'string',   ['notnull' => false, 'default' => 'pending']);
        $this->table->addColumn('phpTarget',  'string',   ['notnull' => false]);
    }

    /**
     * {@inheritdoc}
     */
    protected function addIndexes()
    {
        $this->table->addIndex(['package_id']);
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
        $this->table->addForeignKeyConstraint('bolt_marketplace_package', ['package_id'], ['id']);
    }
}
