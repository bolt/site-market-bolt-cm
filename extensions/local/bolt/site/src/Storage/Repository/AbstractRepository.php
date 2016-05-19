<?php

namespace Bolt\Extension\Bolt\MarketPlace\Storage\Repository;

use Bolt\Storage\Repository;

/**
 * Base repository for the Market Place.
 *
 * @author Gawain Lynch <gawain.lynch@gmail.com>
 */
abstract class AbstractRepository extends Repository
{
    /**
     * {@inheritdoc}
     */
    public function createQueryBuilder($alias = null)
    {
        return $this->em->createQueryBuilder()
            ->from($this->getTableName(), $alias);
    }
}
