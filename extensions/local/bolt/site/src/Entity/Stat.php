<?php

namespace Bolt\Extension\Bolt\MarketPlace\Entity;

use Doctrine\Entity\Base as EntityBase;
use Doctrine\ORM\Mapping\Builder\ClassMetadataBuilder;
use Doctrine\ORM\Mapping\ClassMetadata;

class Stat extends EntityBase
{
    protected $id;
    protected $type;
    protected $recorded;
    protected $source;
    protected $ip;
    protected $package;
    protected $version;
    protected $account;
    
    
    
    public static function loadMetadata(ClassMetadata $metadata)
    {
        $builder = new ClassMetadataBuilder($metadata);
        
        $builder->createField('id',         'guid')->isPrimaryKey()->generatedValue('UUID')->build();
        
        $builder->addField('type',     'string',   ['nullable' => true]);
        $builder->addField('source',   'string',   ['nullable' => true]);
        $builder->addField('ip',       'string',   ['nullable' => true]);
        $builder->addField('recorded', 'datetime', ['nullable' => true]);
        $builder->addField('version',  'string',   ['nullable' => true]);
        
        $builder->addManyToOne('package', 'Bolt\Extensions\Entity\Package');
        $builder->addManyToOne('account', 'Bolt\Extensions\Entity\Account');
    }
}
