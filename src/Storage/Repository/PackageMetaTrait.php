<?php

namespace Bolt\Extension\Bolt\MarketPlace\Storage\Repository;

use Bolt\Extension\Bolt\MarketPlace\Storage\Entity;
use Bolt\Storage\EntityManager;
use Doctrine\DBAL\Query\QueryBuilder;

/**
 * Trait for Package fetching from certain repositories.
 *
 * @author Gawain Lynch <gawain.lynch@gmail.com>
 */
trait PackageMetaTrait
{
    /**
     * Get ranked package entities.
     *
     * @param int $limit
     *
     * @return Entity\Package[]|false
     */
    public function getRankedPackages($limit = 10)
    {
        /** @var Package $packageRepo */
        $packageRepo = $this->getEntityManager()->getRepository(Entity\Package::class);
        $query = $this->getRankedPackagesQuery($packageRepo, $limit);

        return $packageRepo->findWith($query);
    }

    /**
     * @param Package $packageRepo
     * @param int     $limit
     *
     * @return QueryBuilder
     */
    public function getRankedPackagesQuery(Package $packageRepo, $limit)
    {
        /** @var QueryBuilder $qb */
        $qb = $packageRepo->createQueryBuilder('p')
            ->select('p.*')
            ->leftJoin('p', $this->getTableName(), 's', 'p.id = s.package_id')
            ->addSelect('COUNT(s.id) as count')
            ->andWhere('p.approved = :approved')
            ->setParameter('approved', true)
            ->setMaxResults($limit)
            ->groupBy('s.id, p.id')
            ->orderBy('count', 'DESC')
        ;

        return $qb;
    }

    /**
     * @return EntityManager
     */
    abstract public function getEntityManager();

    /**
     * @return string
     */
    abstract public function getTableName();
}
