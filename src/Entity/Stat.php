<?php

namespace Bolt\Extensions\Entity;

use Doctrine\ORM\Mapping\Builder\ClassMetadataBuilder;
use Doctrine\ORM\Mapping\ClassMetadata;
use Doctrine\Entity\Base as EntityBase;


class Stat extends EntityBase {

    protected $id;
    protected $type;
    protected $recorded;
    protected $source;
    protected $ip;
    protected $package;


    public static function loadMetadata(ClassMetadata $metadata)
    {
        $builder = new ClassMetadataBuilder($metadata);
        $builder->createField('id',         'guid')->isPrimaryKey()->generatedValue("UUID")->build();
        $builder->addField('type',          'string',   ['nullable'=>true]);
        $builder->addField('source',        'string',   ['nullable'=>true]);
        $builder->addField('ip',            'string',   ['nullable'=>true]);
        $builder->addField('recorded',      'datetime', ['nullable'=>true]);
        $builder->addManyToOne('package',   'Bolt\Extensions\Entity\Package');


    }


}