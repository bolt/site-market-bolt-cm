<?php

namespace Bolt\Extension\Bolt\MarketPlace\Storage\Entity;

use Bolt\Storage\Entity\Entity;

/**
 * Package version entity.
 *
 * @author Gawain Lynch <gawain.lynch@gmail.com>
 */
class PackageVersions extends Entity
{
    /** @var string */
    protected $id;
    /** @var string */
    protected $package_id;
    /** @var string */
    protected $version;
    /** @var string */
    protected $pretty_version;
    /** @var string */
    protected $stability;
    /** @var string */
    protected $bolt_min;
    /** @var string */
    protected $bolt_max;

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

    /**
     * @return string
     */
    public function getPrettyVersion()
    {
        return $this->pretty_version;
    }

    /**
     * @param string $pretty_version
     */
    public function setPrettyVersion($pretty_version)
    {
        $this->pretty_version = $pretty_version;
    }

    /**
     * @return string
     */
    public function getStability()
    {
        return $this->stability;
    }

    /**
     * @param string $stability
     */
    public function setStability($stability)
    {
        $this->stability = $stability;
    }

    /**
     * @return string
     */
    public function getBoltMin()
    {
        return $this->bolt_min;
    }

    /**
     * @param string $boltMin
     */
    public function setBoltMin($boltMin)
    {
        $this->bolt_min = $boltMin;
    }

    /**
     * @return string
     */
    public function getBoltMax()
    {
        return $this->bolt_max;
    }

    /**
     * @param string $boltMax
     */
    public function setBoltMax($boltMax)
    {
        $this->bolt_max = $boltMax;
    }
}
