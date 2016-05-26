<?php

namespace Bolt\Extension\Bolt\MarketPlace\Storage\Repository;

use Bolt\Extension\Bolt\MarketPlace\Storage\Entity;
use Bolt\Storage\Repository;

/**
 * Stat repository.
 *
 * @author Gawain Lynch <gawain.lynch@gmail.com>
 * @author Ross Riley <riley.ross@gmail.com>
 */
class Stat extends AbstractRepository
{
    /**
     * @param Entity\Package $package
     * @param string         $version
     * @param \DateTime|null $from
     * @param \DateTime|null $to
     *
     * @return Entity\Stat[]
     */
    public function getStats(Entity\Package $package, $version, \DateTime $from = null, \DateTime $to = null)
    {
        $qb = $this->getStatsQuery($package, $version, $from, $to);
        $stats = $this->findWith($qb);

        return $stats;
    }

    public function getStatsQuery(Entity\Package $package, $version, \DateTime $from = null, \DateTime $to = null)
    {
        $qb = $this->createQueryBuilder('s')
            ->select('*')
            ->where('s.type = :type')
            ->andWhere('s.package_id = :package_id')
            ->setParameter('type', 'install')
            ->setParameter('package_id', $package->getId());

        if ($from != null && $to != null) {
            $from = $from->format('Y-m-d H:i:s');
            $to = $to->format('Y-m-d H:i:s');

            $qb = $qb
                ->andWhere('s.recorded >= :from')
                ->andWhere('s.recorded < :to')
                ->setParameter('from', $from)
                ->setParameter('to', $to);
        }

        if ($version != null && $version != '') {
            $qb = $qb
                ->andWhere('s.version = :version')
                ->setParameter('version', $version);
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

        /** @var Entity\Stat $statEntity */
        foreach ($statEntities as $statEntity) {
            $versions[] = $statEntity->getVersion();
        }

        return $versions;
    }

    public function getAllVersionsQuery($packageId)
    {
        $qb = $this->createQueryBuilder('s')
            ->select('s.*')
            ->where('s.type = :type')
            ->andWhere('s.package_id = :package_id')
            ->setParameter('type', 'install')
            ->setParameter('package_id', $packageId)
            ->groupBy('s.version, s.id');

        return $qb;
    }
}
