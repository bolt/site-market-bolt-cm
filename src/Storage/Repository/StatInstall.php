<?php

namespace Bolt\Extension\Bolt\MarketPlace\Storage\Repository;

use Bolt\Extension\Bolt\MarketPlace\Storage\Entity;
use Doctrine\DBAL\Query\QueryBuilder;

/**
 * Install statistics repository.
 *
 * @author Gawain Lynch <gawain.lynch@gmail.com>
 * @author Ross Riley <riley.ross@gmail.com>
 */
class StatInstall extends AbstractRepository
{
    use PackageMetaTrait;

    /**
     * Get a package's install stats.
     *
     * @param string $packageId
     * @param string $version
     *
     * @return Entity\StatInstall[]
     */
    public function getInstalls($packageId, $version = null)
    {
        $query = $this->getInstallsQuery($packageId, $version);

        return $this->findWith($query);
    }

    public function getInstallsQuery($packageId, $version)
    {
        /** @var QueryBuilder $qb */
        $qb = $this->createQueryBuilder('s')
            ->select('s.*')->where('s.package_id = :packageId')
            ->setParameter('packageId', $packageId)
            ->groupBy('ip')
        ;

        if ($version !== null) {
            $qb
                ->andWhere('s.version = :version')
                ->setParameter('version', $version)
            ;
        }

        return $qb;
    }

    /**
     * Get a package's install count.
     *
     * @param string $packageId
     * @param string $version
     *
     * @return integer
     */
    public function getInstallsCount($packageId, $version = null)
    {
        $query = $this->getInstallsCountQuery($packageId, $version);

        return $this->getCount($query->execute()->fetch());
    }

    public function getInstallsCountQuery($packageId, $version)
    {
        /** @var QueryBuilder $qb */
        $qb = $this->createQueryBuilder('s')
            ->select('COUNT(s.id) as count')
            ->where('s.package_id = :packageId')
            ->setParameter('packageId', $packageId)
        ;

        if ($version !== null) {
            $qb
                ->andWhere('s.version = :version')
                ->setParameter('version', $version)
            ;
        }

        return $qb;
    }

    /**
     * @param Entity\Package $package
     * @param string         $version
     * @param \DateTime|null $from
     * @param \DateTime|null $to
     *
     * @return Entity\StatInstall[]
     */
    public function getStats(Entity\Package $package, $version, \DateTime $from = null, \DateTime $to = null)
    {
        $query = $this->getStatsQuery($package, $version, $from, $to);

        return $this->findWith($query);
    }

    public function getStatsQuery(Entity\Package $package, $version, \DateTime $from = null, \DateTime $to = null)
    {
        /** @var QueryBuilder $qb */
        $qb = $this->createQueryBuilder('s')
            ->select('s.*')
            ->andWhere('s.package_id = :packageId')
            ->setParameter('packageId', $package->getId());

        if ($from != null && $to != null) {
            $from = $from->format('Y-m-d H:i:s');
            $to = $to->format('Y-m-d H:i:s');

            $qb
                ->andWhere('s.recorded >= :from')
                ->andWhere('s.recorded < :to')
                ->setParameter('from', $from)
                ->setParameter('to', $to)
            ;
        }

        if ($version != null && $version != '') {
            $qb
                ->andWhere('s.version = :version')
                ->setParameter('version', $version)
            ;
        }

        return $qb;
    }

    /**
     * @param string $packageId
     *
     * @return array
     */
    public function getAllVersions($packageId)
    {
        $versions = [];
        $qb = $this->getAllVersionsQuery($packageId);

        $statEntities = $this->findWith($qb);
        if ($statEntities === false) {
            return $versions;
        }

        /** @var Entity\StatInstall $statEntity */
        foreach ($statEntities as $statEntity) {
            $versions[] = $statEntity->getVersion();
        }

        return $versions;
    }

    public function getAllVersionsQuery($packageId)
    {
        /** @var QueryBuilder $qb */
        $qb = $this->createQueryBuilder('s')
            ->select('s.*')
            ->andWhere('s.package_id = :packageId')
            ->setParameter('packageId', $packageId)
            ->groupBy('s.version, s.id')
        ;

        return $qb;
    }

    /**
     * {@inheritdoc}
     *
     * @param string $type
     */
    public function getRankedPackages($limit = 10, $type = null)
    {
        /** @var Package $packageRepo */
        $packageRepo = $this->getEntityManager()->getRepository(Entity\Package::class);
        $query = $this->getRankedPackagesQuery($packageRepo, $limit);
        if ($type !== null) {
            $query
                //->select('p.*')
                ->andWhere('p.type = :type')
                ->setParameter('type', $type)
            ;
        }

        return $packageRepo->findWith($query);
    }
}
