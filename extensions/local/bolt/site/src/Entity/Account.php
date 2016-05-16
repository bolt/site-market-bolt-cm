<?php

namespace Bolt\Extension\Bolt\MarketPlace\Entity;

use Doctrine\Entity\Base as EntityBase;
use Doctrine\ORM\Mapping\Builder\ClassMetadataBuilder;
use Doctrine\ORM\Mapping\ClassMetadata;

class Account extends EntityBase {

    protected $id;
    protected $email;
    protected $username;
    protected $password;
    protected $name;
    protected $admin;
    protected $approved;
    protected $created;
    protected $token;
    protected $tokenvalid;
    protected $packages;

    
    public function setPassword($password)
    {
        if(substr($password,0,1) !== "$2") {
            $this->password = password_hash($password, PASSWORD_DEFAULT);
        }
    }
    
    
    public static function loadMetadata(ClassMetadata $metadata)
    {
        $builder = new ClassMetadataBuilder($metadata);
        $builder->createField('id',         'guid')->makePrimaryKey()->generatedValue("UUID")->build();
        $builder->addField('email',         'string',   ['nullable'=>true]);
        $builder->addField('username',      'string',   ['nullable'=>true]);
        $builder->addField('password',      'string',   ['nullable'=>true]);
        $builder->addField('name',          'string',   ['nullable'=>true]);
        $builder->addField('admin',         'boolean',  ['nullable'=>true, 'default'=>false]);
        $builder->addField('approved',      'boolean',  ['nullable'=>true, 'default'=>true]);
        $builder->addField('created',       'datetime', ['nullable'=>true]);
        $builder->addField('token',         'string', ['nullable'=>true]);
        $builder->addField('tokenvalid',    'datetime', ['nullable'=>true]);
        $builder->addOneToMany('packages',  'Bolt\Extensions\Entity\Package', 'account');

    }


}