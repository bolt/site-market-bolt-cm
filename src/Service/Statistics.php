<?php

namespace Bolt\Extension\Bolt\MarketPlace\Service;

use Bolt\Extension\Bolt\MarketPlace\Storage\Entity;
use Bolt\Extension\Bolt\MarketPlace\Storage\Repository;

/**
 * Statistics service.
 *
 * @author Gawain Lynch <gawain.lynch@gmail.com>
 */
class Statistics
{
    /** @var Repository\StatInstall */
    private $repo;
    /** @var Entity\StatInstall[] */
    private $stats;

    /**
     * Constructor.
     *
     * @param Repository\StatInstall $repo
     */
    public function __construct(Repository\StatInstall $repo)
    {
        $this->repo = $repo;
    }

    /**
     * @param string $packageId
     * @param string $version
     *
     * @return int
     */
    public function getDownloads($packageId, $version = null)
    {
        $downloads = [];
        $dcount = 0;
        foreach ($this->getStats($packageId) as $stat) {
            if ($stat->getType() === 'install') {
                $downloads[$stat->getVersion()][$stat->getIp()] = 1;
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

    /**
     * @param string $packageId
     *
     * @return int
     */
    public function getStars($packageId)
    {
        $stars = 0;
        foreach ($this->getStats($packageId) as $stat) {
            if ($stat->getType() === 'star') {
                $stars ++;
            }
        }

        return $stars;
    }

    /**
     * @param string $packageId
     * @param string $userId
     *
     * @return bool
     */
    public function isStarredBy($packageId, $userId)
    {
        $starred = false;
        foreach ($this->getStats($packageId) as $stat) {
            if ($stat->getType() === 'star' && $stat->getAccountId() === $userId) {
                $starred = true;
            }
        }

        return $starred;
    }

    /**
     * @param string $packageId
     *
     * @return Entity\StatInstall[]
     */
    protected function getStats($packageId)
    {
        if ($this->stats === null) {
            $this->stats = $this->repo->findBy(['package_id' => $packageId]) ?: [];
        }

        return $this->stats;
    }
}
