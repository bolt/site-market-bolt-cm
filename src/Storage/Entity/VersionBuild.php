<?php

namespace Bundle\Site\MarketPlace\Storage\Entity;

use Bolt\Storage\Entity\Entity;

/**
 * VersionBuild entity.
 *
 * @author Ross Riley <riley.ross@gmail.com>
 * @author Gawain Lynch <gawain.lynch@gmail.com>
 */
class VersionBuild extends Entity
{
    /** @var string */
    protected $id;
    /** @var string */
    protected $package_id;
    /** @var string */
    protected $version;
    /** @var string */
    protected $status;
    /** @var \DateTime */
    protected $lastrun;
    /** @var string */
    protected $url;
    /** @var string */
    protected $hash;
    /** @var array */
    protected $testResult;
    /** @var string */
    protected $testStatus;
    /** @var string */
    protected $phpTarget;

    protected $built;
    protected $package;

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
    public function getStatus()
    {
        return $this->status;
    }

    /**
     * @param string $status
     */
    public function setStatus($status)
    {
        $this->status = $status;
    }

    /**
     * @return \DateTime
     */
    public function getLastrun()
    {
        return $this->lastrun;
    }

    /**
     * @param \DateTime $lastrun
     */
    public function setLastrun($lastrun)
    {
        $this->lastrun = $lastrun;
    }

    /**
     * @return string
     */
    public function getUrl()
    {
        return $this->url;
    }

    /**
     * @param string $url
     */
    public function setUrl($url)
    {
        $this->url = $url;
    }

    /**
     * @return string
     */
    public function getHash()
    {
        return $this->hash;
    }

    /**
     * @param string $hash
     */
    public function setHash($hash)
    {
        $this->hash = $hash;
    }

    /**
     * @return array
     */
    public function getTestResult()
    {
        return $this->testResult;
    }

    /**
     * @param array $testResult
     */
    public function setTestResult(array $testResult)
    {
        $this->testResult = $testResult;
    }

    /**
     * @return string
     */
    public function getTestStatus()
    {
        return $this->testStatus;
    }

    /**
     * @param string $testStatus
     */
    public function setTestStatus($testStatus)
    {
        $this->testStatus = $testStatus;
    }

    /**
     * @return string
     */
    public function getPhpTarget()
    {
        return $this->phpTarget;
    }

    /**
     * @param string $phpTarget
     */
    public function setPhpTarget($phpTarget)
    {
        $this->phpTarget = $phpTarget;
    }
}
