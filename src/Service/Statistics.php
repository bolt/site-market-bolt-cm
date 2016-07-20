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
    private $repoStatInstall;
    /** @var Repository\StatWebhook */
    private $repoStatWebhook;
    /** @var Repository\PackageStar */
    private $repoPackageStar;
    /** @var Entity\StatInstall[] */
    private $stats;

    /**
     * Constructor.
     *
     * @param Repository\StatInstall $repoStatInstall
     * @param Repository\StatWebhook $repoStatWebhook
     * @param Repository\PackageStar $repoPackageStar
     */
    public function __construct(
        Repository\StatInstall $repoStatInstall,
        Repository\StatWebhook $repoStatWebhook,
        Repository\PackageStar $repoPackageStar
    ) {
        $this->repoStatInstall = $repoStatInstall;
        $this->repoStatWebhook = $repoStatWebhook;
        $this->repoPackageStar = $repoPackageStar;
    }

    /**
     * @param string $packageId
     * @param string $version
     *
     * @return int
     */
    public function getDownloads($packageId, $version = null)
    {
        if ($version) {
            return $this->repoStatInstall->getInstallsCount($packageId, $version);
        }

        return $this->repoStatInstall->getInstallsCount($packageId);
    }

    /**
     * @param string $packageId
     *
     * @return int
     */
    public function getStars($packageId)
    {
        $stars = $this->repoPackageStar->getStarsCount($packageId);

        return $stars;
    }

    /**
     * @param string $packageId
     * @param string $accountId
     *
     * @return bool
     */
    public function isStarredBy($packageId, $accountId)
    {
        return $this->repoPackageStar->isStarredBy($packageId, $accountId);
    }
}
