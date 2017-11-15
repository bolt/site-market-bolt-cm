<?php

namespace Bundle\Site\MarketPlace\Storage\Repository;

use Bundle\Site\MarketPlace\Storage\Entity;
use Doctrine\DBAL\Query\QueryBuilder;

/**
 * Webhook statistics repository.
 *
 * @author Gawain Lynch <gawain.lynch@gmail.com>
 */
class StatWebhook extends AbstractRepository
{
    /**
     * @param string $packageId
     *
     * @return Entity\StatWebhook
     */
    public function getLatest($packageId)
    {
        $qb = $this->getLatestQuery($packageId);

        return $this->findOneWith($qb);
    }

    public function getLatestQuery($packageId)
    {
        /** @var QueryBuilder $qb */
        $qb = $this->createQueryBuilder('s')
            ->select('*')
            ->andWhere('s.package_id = :package_id')
            ->setParameter('package_id', $packageId)
            ->orderBy('recorded', 'DESC')
        ;

        return $qb;
    }
}
