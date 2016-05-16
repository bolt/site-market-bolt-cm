<?php

namespace Bolt\Extension\Bolt\MarketPlace\Entity;

use Doctrine\Entity\Base as EntityBase;
use Doctrine\ORM\Mapping\Builder\ClassMetadataBuilder;
use Doctrine\ORM\Mapping\ClassMetadata;

class Package extends EntityBase {

    protected $id;
    protected $title;
    protected $source;
    protected $name;
    protected $keywords;
    protected $type;
    protected $description;
    protected $documentation;
    protected $approved;
    protected $requirements;
    protected $versions;
    protected $created;
    protected $updated;
    protected $authors;
    protected $account;
    protected $token;
    protected $stats;
    protected $builds;
    protected $screenshots;
    protected $icon;
    protected $support;
    protected $suggested;


    public function setSource($value)
    {
        $this->source = rtrim($value, "/");
    }

    public function getSource()
    {
        if(! $this->source){
            return null;
        }

        return dirname($this->source)."/".basename($this->source, '.git');
    }

    public function getRawSource()
    {
        return $this->source;
    }

    public function setName($value)
    {
        $this->name = strtolower($value);
    }

    public function setSupport($value)
    {
        $this->support = json_encode($value);
    }

    public function getSupport()
    {
        json_decode($this->support, true);
    }

    public function setSuggested($value)
    {
        $this->suggested = json_encode($value);
    }

    public function getSuggested()
    {
        if ($this->suggested) {
            return json_decode($this->suggested, true);
        }

        return [];
    }

    public function getKeywords()
    {
        return array_filter(explode(",",$this->keywords));
    }

    public function getVersions()
    {
        return array_filter(explode(",",$this->versions));
    }

    public function getRequirements()
    {
        return json_decode($this->requirements, true);
    }

    public function getScreenshots()
    {
        return array_filter(explode(",",$this->screenshots));
    }



    public function regenerateToken()
    {
        $this->token = bin2hex(openssl_random_pseudo_bytes(16));
    }

    public function getDownloads($version = false)
    {
        $downloads = [];
        $dcount = 0;
        foreach ($this->stats as $stat) {
            if($stat->type == 'install') {
                $downloads[$stat->version][$stat->ip] = 1;
                $dcount ++ ;
            }
        }
        foreach($downloads as $ver=>$hits) {
            $downloads[$ver] = count($hits);
        }

        if($version && isset($downloads[$version])) {
            return $downloads[$version];
        }


        return $dcount;

    }

    public function getStars()
    {
        $stars = 0;
        foreach ($this->stats as $stat) {
            if($stat->type == 'star') {
                $stars ++;
            }
        }
        return $stars;
    }

    public function isStarredBy($user)
    {
        $starred = false;
        foreach ($this->stats as $stat) {
            if($stat->type == 'star' && $stat->account === $user) {
                $starred = true;
            }
        }
        return $starred;
    }

    public function serializeAccount()
    {
        return $this->account->id;
    }

    public function serializeToken()
    {
        return '';
    }



    public static function loadMetadata(ClassMetadata $metadata)
    {
        $builder = new ClassMetadataBuilder($metadata);
        $builder->createField('id',         'guid')->makePrimaryKey()->generatedValue("UUID")->build();
        $builder->addField('source',        'string',   ['nullable'=>true]);
        $builder->addField('title',         'string',   ['nullable'=>true]);
        $builder->addField('name',          'string',   ['nullable'=>true]);
        $builder->addField('keywords',      'string',   ['nullable'=>true]);
        $builder->addField('type',          'string',   ['nullable'=>true]);
        $builder->addField('description',   'text',     ['nullable'=>true]);
        $builder->addField('documentation', 'text',     ['nullable'=>true]);
        $builder->addField('approved',      'boolean',  ['nullable'=>true, 'default'=>true]);
        $builder->addField('versions',      'string',   ['nullable'=>true]);
        $builder->addField('requirements',  'string',   ['nullable'=>true]);
        $builder->addField('authors',       'string',   ['nullable'=>true]);
        $builder->addField('created',       'datetime', ['nullable'=>true]);
        $builder->addField('updated',       'datetime', ['nullable'=>true]);
        $builder->addField('token',         'string',   ['nullable'=>true]);
        $builder->addField('screenshots',   'text',     ['nullable'=>true]);
        $builder->addField('icon',          'text',     ['nullable'=>true]);
        $builder->addField('support',       'text',     ['nullable'=>true]);
        $builder->addField('suggested',     'json_array',     ['nullable'=>true]);
        $builder->addManyToOne('account',   'Bolt\Extensions\Entity\Account');
        $builder->addOneToMany('stats',     'Bolt\Extensions\Entity\Stat', 'package');
        $builder->addOneToMany('builds',     'Bolt\Extensions\Entity\VersionBuild', 'package');
        $builder->setCustomRepositoryClass('Bolt\Extensions\Repository\Package');
    }


}