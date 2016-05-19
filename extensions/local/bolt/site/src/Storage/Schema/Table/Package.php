<?php

namespace Bolt\Extension\Bolt\MarketPlace\Storage\Schema\Table;

use Bolt\Storage\Database\Schema\Table\BaseTable;

/**
 * Package table.
 *
 * @author Gawain Lynch <gawain.lynch@gmail.com>
 */
class Package extends BaseTable
{
    /**
     * {@inheritdoc}
     */
    protected function addColumns()
    {
        $this->table->addColumn('id',            'guid',       []);
        $this->table->addColumn('account_id',    'guid',       []);
        $this->table->addColumn('source',        'string',     ['notnull' => false]);
        $this->table->addColumn('title',         'string',     ['notnull' => false]);
        $this->table->addColumn('name',          'string',     ['notnull' => false]);
        $this->table->addColumn('keywords',      'string',     ['notnull' => false]);
        $this->table->addColumn('type',          'string',     ['notnull' => false]);
        $this->table->addColumn('description',   'text',       ['notnull' => false]);
        $this->table->addColumn('documentation', 'text',       ['notnull' => false]);
        $this->table->addColumn('approved',      'boolean',    ['notnull' => false, 'default' => true]);
        $this->table->addColumn('versions',      'string',     ['notnull' => false]);
        $this->table->addColumn('requirements',  'string',     ['notnull' => false]);
        $this->table->addColumn('authors',       'string',     ['notnull' => false]);
        $this->table->addColumn('created',       'datetime',   ['notnull' => false]);
        $this->table->addColumn('updated',       'datetime',   ['notnull' => false]);
        $this->table->addColumn('token',         'string',     ['notnull' => false]);
        $this->table->addColumn('screenshots',   'text',       ['notnull' => false]);
        $this->table->addColumn('icon',          'text',       ['notnull' => false]);
        $this->table->addColumn('support',       'text',       ['notnull' => false]);
        $this->table->addColumn('suggested',     'json_array', ['notnull' => false]);
    }

    /**
     * {@inheritdoc}
     */
    protected function addIndexes()
    {
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
        $this->table->addForeignKeyConstraint('bolt_members_account', ['account_id'], ['guid']);
    }
}