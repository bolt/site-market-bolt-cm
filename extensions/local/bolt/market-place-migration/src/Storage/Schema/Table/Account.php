<?php

namespace Bolt\Extension\Bolt\MarketPlaceMigration\Storage\Schema\Table;

use Bolt\Storage\Database\Schema\Table\BaseTable;

/**
 * Account table.
 *
 * @author Gawain Lynch <gawain.lynch@gmail.com>
 */
class Account extends BaseTable
{
    /**
     * {@inheritdoc}
     */
    protected function addColumns()
    {
        $this->table->addColumn('id',         'guid',     []);
        $this->table->addColumn('email',      'string',   ['notnull' => false]);
        $this->table->addColumn('username',   'string',   ['notnull' => false]);
        $this->table->addColumn('password',   'string',   ['notnull' => false]);
        $this->table->addColumn('name',       'string',   ['notnull' => false]);
        $this->table->addColumn('admin',      'boolean',  ['notnull' => false, 'default' => false]);
        $this->table->addColumn('approved',   'boolean',  ['notnull' => false, 'default' => true]);
        $this->table->addColumn('created',    'datetime', ['notnull' => false]);
        $this->table->addColumn('token',      'string',   ['notnull' => false]);
        $this->table->addColumn('tokenvalid', 'datetime', ['notnull' => false]);
    }

    /**
     * {@inheritdoc}
     */
    protected function addIndexes()
    {
        $this->table->addIndex(['email']);
        $this->table->addIndex(['username']);
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
        //$builder->addOneToMany('packages', 'Bolt\Extensions\Entity\Package', 'account');
    }
}
