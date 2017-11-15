<?php

namespace Bundle\Site\MarketPlace\Service;

use Bundle\Site\MarketPlace\Storage\Entity;
use Bundle\Site\MarketPlace\Storage\Repository;
use Bolt\Storage\EntityManager;

/**
 * Statistics service.
 *
 * @author Gawain Lynch <gawain.lynch@gmail.com>
 */
class Statistics
{
    /** @var EntityManager */
    private $em;

    /**
     * Constructor.
     *
     * @param EntityManager $em
     */
    public function __construct(EntityManager $em)
    {
        $this->em = $em;
    }

    /**
     * @param string $packageId
     * @param string $version
     *
     * @return int
     */
    public function getDownloads($packageId, $version = null)
    {
        /** @var Repository\StatInstall $repo */
        $repo = $this->em->getRepository(Entity\StatInstall::class);
        if ($version) {
            return $repo->getInstallsCount($packageId, $version);
        }

        return $repo->getInstallsCount($packageId);
    }

    /**
     * @param string $packageId
     *
     * @return int
     */
    public function getStars($packageId)
    {
        /** @var Repository\PackageStar $repo */
        $repo = $this->em->getRepository(Entity\PackageStar::class);

        return $repo->getStarsCount($packageId);
    }

    /**
     * @param string $packageId
     * @param string $accountId
     *
     * @return bool
     */
    public function isStarredBy($packageId, $accountId)
    {
        /** @var Repository\PackageStar $repo */
        $repo = $this->em->getRepository(Entity\PackageStar::class);

        return $repo->isStarredBy($packageId, $accountId);
    }
}
