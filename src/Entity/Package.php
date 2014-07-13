<?php
namespace Bolt\Extensions\Entity;

use Doctrine\ORM\Mapping\Builder\ClassMetadataBuilder;
use Doctrine\ORM\Mapping\ClassMetadata;

use Composer\IO\NullIO;
use Composer\Factory;
use Composer\Repository\VcsRepository;


class Package extends Base {

    protected $id;
    protected $title;
    protected $source;
    protected $name;
    protected $keywords;
    protected $description;
    protected $approved;
    protected $versions;

    public static function loadMetadata(ClassMetadata $metadata)
    {
        $builder = new ClassMetadataBuilder($metadata);
        $builder->createField('id',                         'guid')->isPrimaryKey()->generatedValue("UUID")->build();
        $builder->addField('source',                        'string',   ['nullable'=>true]);
        $builder->addField('title',                         'string',   ['nullable'=>true]);
        $builder->addField('name',                          'string',   ['nullable'=>true]);
        $builder->addField('keywords',                      'string',   ['nullable'=>true]);
        $builder->addField('description',                   'text',     ['nullable'=>true]);
        $builder->addField('approved',                      'boolean',  ['nullable'=>true, 'default'=>true]);
        $builder->addField('versions',                      'string',   ['nullable'=>true]);

    }
    
    public function setSource($value)
    {
        $this->source = rtrim($value, "/");
    }
    
    public function sync()
    {
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
        if(isset($information['keywords'])) {
            $this->setKeywords(implode(',',$information['keywords']));
        }
        $this->setVersions(implode(',', $pv));        
    }


}