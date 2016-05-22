<?php

namespace Bolt\Extension\Bolt\MarketPlace\Storage\Entity;

use Bolt\Storage\Entity\Entity;

class Package extends Entity
{
    /** @var string */
    protected $id;
    /** @var string */
    protected $account_id;
    /** @var string */
    protected $source;
    /** @var string */
    protected $title;
    /** @var string */
    protected $name;
    /** @var string */
    protected $keywords;
    /** @var string */
    protected $type;
    /** @var string */
    protected $description;
    /** @var string */
    protected $documentation;
    /** @var boolean */
    protected $approved;
    /** @var string */
    protected $requirements;
    /** @var string */
    protected $versions;
    /** @var \DateTime */
    protected $created;
    /** @var \DateTime */
    protected $updated;
    /** @var string */
    protected $authors;
    /** @var string */
    protected $token;
    /** @var string */
    protected $screenshots;
    /** @var string */
    protected $icon;
    /** @var string */
    protected $support;
    /** @var array */
    protected $suggested;

    protected $account;
    protected $stats;
    protected $builds;

    /**
     * @return string
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @param string $id
     */
    public function setId($id)
    {
        $this->id = $id;
    }

    /**
     * @return string
     */
    public function getAccountId()
    {
        return $this->account_id;
    }

    /**
     * @param string $account_id
     */
    public function setAccountId($account_id)
    {
        $this->account_id = $account_id;
    }

    /**
     * @return string
     */
    public function getTitle()
    {
        return $this->title;
    }

    /**
     * @param string $title
     */
    public function setTitle($title)
    {
        $this->title = $title;
    }

    /**
     * @return string
     */
    public function getSource()
    {
        if ($this->source === null) {
            return null;
        }

        return dirname($this->source) . '/' . basename($this->source, '.git');
    }

    /**
     * @return string
     */
    public function getRawSource()
    {
        return $this->source;
    }

    /**
     * @param string $source
     */
    public function setSource($source)
    {
        $this->source = rtrim($source, '/');
    }

    /**
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * @param string $name
     */
    public function setName($name)
    {
        $this->name = strtolower($name);
    }

    /**
     * @return string
     */
    public function getKeywords()
    {
        return $this->keywords;
    }

    /**
     * @param string $keywords
     */
    public function setKeywords($keywords)
    {
        $this->keywords = $keywords;
    }

    /**
     * @return string
     */
    public function getType()
    {
        return $this->type;
    }

    /**
     * @param string $type
     */
    public function setType($type)
    {
        $this->type = $type;
    }

    /**
     * @return string
     */
    public function getDescription()
    {
        return $this->description;
    }

    /**
     * @param string $description
     */
    public function setDescription($description)
    {
        $this->description = $description;
    }

    /**
     * @return string
     */
    public function getDocumentation()
    {
        return $this->documentation;
    }

    /**
     * @param string $documentation
     */
    public function setDocumentation($documentation)
    {
        $this->documentation = $documentation;
    }

    /**
     * @return boolean
     */
    public function isApproved()
    {
        return $this->approved;
    }

    /**
     * @param boolean $approved
     */
    public function setApproved($approved)
    {
        $this->approved = $approved;
    }

    /**
     * @return string
     */
    public function getRequirements()
    {
        return json_decode($this->requirements, true);
    }

    /**
     * @param string $requirements
     */
    public function setRequirements($requirements)
    {
        $this->requirements = $requirements;
    }

    /**
     * @return string
     */
    public function getVersions()
    {
        return $this->versions;
    }

    /**
     * @param string $versions
     */
    public function setVersions($versions)
    {
        $this->versions = $versions;
    }

    /**
     * @return \DateTime
     */
    public function getCreated()
    {
        return $this->created;
    }

    /**
     * @param \DateTime $created
     */
    public function setCreated($created)
    {
        $this->created = $created;
    }

    /**
     * @return \DateTime
     */
    public function getUpdated()
    {
        return $this->updated;
    }

    /**
     * @param \DateTime $updated
     */
    public function setUpdated($updated)
    {
        $this->updated = $updated;
    }

    /**
     * @return string
     */
    public function getAuthors()
    {
        return $this->authors;
    }

    /**
     * @param string $authors
     */
    public function setAuthors($authors)
    {
        $this->authors = $authors;
    }

    /**
     * @return string
     */
    public function getToken()
    {
        return $this->token;
    }

    /**
     * @param string $token
     */
    public function setToken($token)
    {
        $this->token = $token;
    }

    /**
     * @return string
     */
    public function getScreenshots()
    {
        return $this->screenshots;
    }

    /**
     * @param string $screenshots
     */
    public function setScreenshots($screenshots)
    {
        $this->screenshots = $screenshots;
    }

    /**
     * @return string
     */
    public function getIcon()
    {
        return $this->icon;
    }

    /**
     * @param string $icon
     */
    public function setIcon($icon)
    {
        $this->icon = $icon;
    }

    /**
     * @return string
     */
    public function getSupport()
    {
        return json_decode($this->support, true);
    }

    /**
     * @param string $support
     */
    public function setSupport($support)
    {
        $this->support = json_encode($support);
    }

    /**
     * @return array
     */
    public function getSuggested()
    {
        if ($this->suggested === null) {
            return [];
        }

        return json_decode($this->suggested, true);
    }

    /**
     * @param array $suggested
     */
    public function setSuggested($suggested)
    {
        $this->suggested = json_encode($suggested);
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
            if ($stat->type == 'install') {
                $downloads[$stat->version][$stat->ip] = 1;
                $dcount ++;
            }
        }
        foreach ($downloads as $ver => $hits) {
            $downloads[$ver] = count($hits);
        }

        if ($version && isset($downloads[$version])) {
            return $downloads[$version];
        }

        return $dcount;
    }

    public function getStars()
    {
        $stars = 0;
        foreach ($this->stats as $stat) {
            if ($stat->type == 'star') {
                $stars ++;
            }
        }

        return $stars;
    }

    public function isStarredBy($user)
    {
        $starred = false;
        foreach ($this->stats as $stat) {
            if ($stat->type == 'star' && $stat->account === $user) {
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
}
