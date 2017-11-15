<?php

namespace Bundle\Site\MarketPlace\Storage\Entity;

use Bolt\Storage\Entity\Entity;

/**
 * Package token entity.
 *
 * @author Gawain Lynch <gawain.lynch@gmail.com>
 */
class PackageToken extends Entity
{
    /** @var string */
    protected $id;
    /** @var string */
    protected $package_id;
    /** @var string */
    protected $token;
    /** @var string */
    protected $type;

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
    public function getPackageId()
    {
        return $this->package_id;
    }

    /**
     * @param string $package_id
     */
    public function setPackageId($package_id)
    {
        $this->package_id = $package_id;
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

    public function regenerateToken()
    {
        $this->token = bin2hex(openssl_random_pseudo_bytes(16));
    }
}
