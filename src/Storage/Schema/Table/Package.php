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
        $this->table->addColumn('keywords',      'json_array', ['notnull' => false]);
        $this->table->addColumn('type',          'string',     ['notnull' => false]);
        $this->table->addColumn('description',   'text',       ['notnull' => false]);
        $this->table->addColumn('documentation', 'text',       ['notnull' => false]);
        $this->table->addColumn('approved',      'boolean',    ['notnull' => false, 'default' => true]);
        $this->table->addColumn('authors',       'json_array', ['notnull' => false]);
        $this->table->addColumn('license',       'json_array', ['notnull' => false]);
        $this->table->addColumn('created',       'datetime',   ['notnull' => false]);
        $this->table->addColumn('screenshots',   'json_array', ['notnull' => false]);
        $this->table->addColumn('icon',          'text',       ['notnull' => false]);
        $this->table->addColumn('support',       'json_array', ['notnull' => false]);
        $this->table->addColumn('suggested',     'json_array', ['notnull' => false]);
    }

    /**
     * {@inheritdoc}
     */
    protected function addIndexes()
    {
        $this->table->addUniqueIndex(['source']);
        $this->table->addUniqueIndex(['name']);

        // Indexes for binary JSON arrays
        $this->table->addIndex(['keywords']);
        $this->table->addIndex(['authors']);
        $this->table->addIndex(['license']);
        $this->table->addIndex(['screenshots']);
        $this->table->addIndex(['support']);
        $this->table->addIndex(['suggested']);

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
        $this->table->addForeignKeyConstraint($this->tablePrefix . 'members_account', ['account_id'], ['guid'], ['onDelete' => 'CASCADE']);
    }
}
