<?php

namespace Bolt\Extensions\Entity;

use Doctrine\ORM\Mapping\Builder\ClassMetadataBuilder;
use Doctrine\ORM\Mapping\ClassMetadata;
use Doctrine\Entity\Base as EntityBase;



class VersionBuild extends EntityBase {

    protected $id;
    protected $package;
    protected $version;
    protected $status;
    protected $lastrun;
    protected $url;
    protected $hash;
    protected $built;

    public static function loadMetadata(ClassMetadata $metadata)
    {
        $builder = new ClassMetadataBuilder($metadata);
        $builder->createField('id',         'guid')->isPrimaryKey()->generatedValue("UUID")->build();
        $builder->addField('version',       'string',   ['nullable'=>true]);
        $builder->addField('status',        'string',   ['nullable'=>true]);
        $builder->addField('lastrun',       'datetime', ['nullable'=>true]);
        $builder->addField('url',           'string',   ['nullable'=>true]);
        $builder->addField('hash',          'string',   ['nullable'=>true]);
        $builder->addManyToOne('package',   'Bolt\Extensions\Entity\Package');


    }
    



}