<?php

namespace Bolt\Extension\Bolt\MarketPlace\Storage\Entity;

use Bolt\Storage\Entity\Entity;

/**
 * Package install statistics entity.
 *
 * @author Ross Riley <riley.ross@gmail.com>
 * @author Gawain Lynch <gawain.lynch@gmail.com>
 */
class StatInstall extends Entity
{
    /** @var string */
    protected $id;
    /** @var string */
    protected $package_id;
    /** @var string */
    protected $ip;
    /** @var \DateTime */
    protected $recorded;
    /** @var string */
    protected $version;

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
    public function getIp()
    {
        return $this->ip;
    }

    /**
     * @param string $ip
     */
    public function setIp($ip)
    {
        $this->ip = $ip;
    }

    /**
     * @return \DateTime
     */
    public function getRecorded()
    {
        return $this->recorded;
    }

    /**
     * @param \DateTime $recorded
     */
    public function setRecorded($recorded)
    {
        $this->recorded = $recorded;
    }

    /**
     * @return string
     */
    public function getVersion()
    {
        return $this->version;
    }

    /**
     * @param string $version
     */
    public function setVersion($version)
    {
        $this->version = $version;
    }
}
