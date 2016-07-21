<?php

namespace Bolt\Extension\Bolt\MarketPlace\Storage\Repository;

use Bolt\Extension\Bolt\MarketPlace\Storage\Entity;
use Doctrine\DBAL\Query\QueryBuilder;

/**
 * Package "stars" repository.
 *
 * @author Gawain Lynch <gawain.lynch@gmail.com>
 * @author Ross Riley <riley.ross@gmail.com>
 */
class PackageStar extends AbstractRepository
{
    use PackageMetaTrait;

    /**
     * Get a package's stars.
     *
     * @param string $packageId
     *
     * @return Entity\StatInstall[]
     */
    public function getStars($packageId)
    {
        $query = $this->getStarsQuery($packageId);

        return $this->findWith($query);
    }

    public function getStarsQuery($packageId)
    {
        /** @var QueryBuilder $qb */
        $qb = $this->createQueryBuilder('s')
            ->select('s.*')
            ->where('s.package_id = :packageId')
            ->setParameter('packageId', $packageId)
            ->groupBy('ip')
        ;

        return $qb;
    }

    /**
     * Get a package's star count.
     *
     * @param string $packageId
     *
     * @return integer
     */
    public function getStarsCount($packageId)
    {
        $query = $this->getStarsCountQuery($packageId);

        return $this->getCount($query->execute()->fetch());
    }

    public function getStarsCountQuery($packageId)
    {
        /** @var QueryBuilder $qb */
        $qb = $this->createQueryBuilder('s')
            ->select('COUNT(s.id) as count')
            ->where('s.package_id = :packageId')
            ->setParameter('packageId', $packageId)
        ;

        return $qb;
    }

    /**
     * Check if a package is starred by a user ID.
     *
     * @param string $packageId
     * @param string $accountId
     *
     * @return bool
     */
    public function isStarredBy($packageId, $accountId)
    {
        $query = $this->isStarredByQuery($packageId, $accountId);

        return (bool) $this->getCount($query->execute()->fetch());
    }

    public function isStarredByQuery($packageId, $accountId)
    {
        /** @var QueryBuilder $qb */
        $qb = $this->createQueryBuilder('s')
            ->select('COUNT(s.id) as count')
            ->where('s.package_id = :packageId')
            ->where('s.account_id = :accountId')
            ->setParameter('packageId', $packageId)
            ->setParameter('accountId', $accountId)
        ;

        return $qb;
    }
}
