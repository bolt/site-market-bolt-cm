<?php
namespace Bolt\Extensions\Entity;

use Doctrine\ORM\Mapping\Builder\ClassMetadataBuilder;
use Doctrine\ORM\Mapping\ClassMetadata;


class Package extends Base {

    protected $id;
    protected $name;
    protected $source;
    protected $title;
    protected $description;
    protected $approved;


    public static function loadMetadata(ClassMetadata $metadata)
    {
        $builder = new ClassMetadataBuilder($metadata);
        $builder->createField('id',                         'guid')->isPrimaryKey()->generatedValue("UUID")->build();
        $builder->addField('name',                          'string',   ['nullable'=>true]);
        $builder->addField('source',                        'string',   ['nullable'=>true]);
        $builder->addField('title',                         'string',   ['nullable'=>true]);
        $builder->addField('description',                   'text',     ['nullable'=>true]);
        $builder->addField('approved',                      'boolean',  ['nullable'=>true, 'default'=>false]);

    }


}