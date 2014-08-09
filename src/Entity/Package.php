<?php

namespace Bolt\Extensions\Entity;

use Doctrine\ORM\Mapping\Builder\ClassMetadataBuilder;
use Doctrine\ORM\Mapping\ClassMetadata;
use Doctrine\Entity\Base as EntityBase;

use Composer\IO\NullIO;
use Composer\Factory;
use Composer\Repository\VcsRepository;


class Package extends EntityBase {

    protected $id;
    protected $title;
    protected $source;
    protected $name;
    protected $keywords;
    protected $type;
    protected $description;
    protected $approved;
    protected $versions;
    protected $created;
    protected $updated;
    protected $authors;
    protected $account;
    protected $token;
    protected $stats;

    public static function loadMetadata(ClassMetadata $metadata)
    {
        $builder = new ClassMetadataBuilder($metadata);
        $builder->createField('id',         'guid')->isPrimaryKey()->generatedValue("UUID")->build();
        $builder->addField('source',        'string',   ['nullable'=>true]);
        $builder->addField('title',         'string',   ['nullable'=>true]);
        $builder->addField('name',          'string',   ['nullable'=>true]);
        $builder->addField('keywords',      'string',   ['nullable'=>true]);
        $builder->addField('type',          'string',   ['nullable'=>true]);
        $builder->addField('description',   'text',     ['nullable'=>true]);
        $builder->addField('approved',      'boolean',  ['nullable'=>true, 'default'=>true]);
        $builder->addField('versions',      'string',   ['nullable'=>true]);
        $builder->addField('authors',       'string',   ['nullable'=>true]);
        $builder->addField('created',       'datetime', ['nullable'=>true]);
        $builder->addField('updated',       'datetime', ['nullable'=>true]);
        $builder->addField('token',         'string',   ['nullable'=>true]);
        $builder->addManyToOne('account',   'Bolt\Extensions\Entity\Account');
        $builder->addOneToMany('stats',     'Bolt\Extensions\Entity\Stat', 'package');

    }
    
    public function setSource($value)
    {
        $this->source = rtrim($value, "/");
    }
    
    public function getKeywords()
    {
        return explode(",",$this->keywords);
    }
    
    public function getVersions()
    {
        return explode(",",$this->versions);
    }
    
    public function sync()
    {
        putenv("COMPOSER_HOME=".sys_get_temp_dir());
        $io = new NullIO();
        $config = Factory::createConfig();
        $io->loadConfiguration($config);
            
        $repository = new VcsRepository(['url' => $this->getSource()], $io, $config);
        $driver = $repository->getDriver();
        $information = $driver->getComposerInformation($driver->getRootIdentifier());

        $versions = $repository->getPackages();
        $pv = [];
        foreach($versions as $version) {
            $pv[]=$version->getPrettyVersion();
        }
        
        $this->setName($information['name']);
        if(isset($information['type'])) {
            $this->setType($information['type']);
        }
        if(isset($information['keywords'])) {
            $this->setKeywords(implode(',',$information['keywords']));
        }
        if(isset($information['authors'])) {
            $authors = [];
            foreach($information['authors'] as $author) {
                $authors[]=$author['name'];
            }
            $this->setAuthors(implode(',',$authors));
        }
        $this->setVersions(implode(',', $pv));
        $this->updated = new \DateTime;
        
    }
    
    public function regenerateToken()
    {
        $this->token = bin2hex(openssl_random_pseudo_bytes(16));
    }


}