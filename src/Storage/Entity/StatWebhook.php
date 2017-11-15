<?php

namespace Bundle\Site\MarketPlace\Storage\Entity;

use Bolt\Storage\Entity\Entity;

/**
 * Webhooks statistics entity.
 *
 * @author Gawain Lynch <gawain.lynch@gmail.com>
 */
class StatWebhook extends Entity
{
    /** @var string */
    protected $id;
    /** @var string */
    protected $package_id;
    /** @var string */
    protected $ip;
    /** @var \DateTime */
    protected $recorded;

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
}
