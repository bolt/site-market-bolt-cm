<?php
namespace Bolt\Extensions\Entity;

use Doctrine\ORM\Mapping\Builder\ClassMetadataBuilder;
use Doctrine\ORM\Mapping\ClassMetadata;


class Account extends Base {

    protected $id;
    protected $email;
    protected $password;
    protected $name;
    protected $created;
    protected $packages;


    public static function loadMetadata(ClassMetadata $metadata)
    {
        $builder = new ClassMetadataBuilder($metadata);
        $builder->createField('id',         'guid')->isPrimaryKey()->generatedValue("UUID")->build();
        $builder->addField('email',         'string',   ['nullable'=>true]);
        $builder->addField('password',      'string',   ['nullable'=>true]);
        $builder->addField('name',          'string',   ['nullable'=>true]);
        $builder->addField('created',       'datetime');
        $builder->addOneToMany('packages',  'Bolt\Extensions\Entity\Package', 'user');


    }
    
    public function setPassword($password)
    {
        if(substr($password,0,1) !== "$2") $this->password = password_hash($password, PASSWORD_DEFAULT);
    }


}