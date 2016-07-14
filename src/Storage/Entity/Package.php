<?php

namespace Bolt\Extension\Bolt\MarketPlace\Storage\Entity;

use Bolt\Storage\Entity\Entity;

/**
 * Package entity.
 *
 * @author Ross Riley <riley.ross@gmail.com>
 * @author Gawain Lynch <gawain.lynch@gmail.com>
 */
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
    /** @var array */
    protected $keywords;
    /** @var string */
    protected $type;
    /** @var string */
    protected $description;
    /** @var string */
    protected $documentation;
    /** @var boolean */
    protected $approved;
    /** @var \DateTime */
    protected $created;
    /** @var array */
    protected $authors;
    /** @var string */
    protected $token;
    /** @var array */
    protected $screenshots;
    /** @var string */
    protected $icon;
    /** @var array */
    protected $support;
    /** @var array */
    protected $suggested;

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
     * @return array
     */
    public function getKeywords()
    {
        return $this->keywords;
    }

    /**
     * @param array $keywords
     */
    public function setKeywords(array $keywords)
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
     * @return array
     */
    public function getAuthors()
    {
        return $this->authors;
    }

    /**
     * @param array $authors
     */
    public function setAuthors(array $authors)
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
     * @return array
     */
    public function getScreenshots()
    {
        return $this->screenshots;
    }

    /**
     * @param array $screenshots
     */
    public function setScreenshots(array $screenshots)
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
     * @return array
     */
    public function getSupport()
    {
        return $this->support;
    }

    /**
     * @param array $support
     */
    public function setSupport(array $support)
    {
        $this->support = $support;
    }

    /**
     * @return array
     */
    public function getSuggested()
    {
        if ($this->suggested === null) {
            return [];
        }

        return $this->suggested;
    }

    /**
     * @param array $suggested
     */
    public function setSuggested($suggested)
    {
        $this->suggested = $suggested;
    }

    public function regenerateToken()
    {
        $this->token = bin2hex(openssl_random_pseudo_bytes(16));
    }
}
