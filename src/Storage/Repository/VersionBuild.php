<?php

namespace Bundle\Site\MarketPlace\Storage\Repository;

use Bolt\Storage\Repository;

/**
 * VersionBuild repository.
 *
 * @author Gawain Lynch <gawain.lynch@gmail.com>
 */
class VersionBuild extends AbstractRepository
{
    /**
     * {@inheritdoc}
     */
    protected function getLoadQuery()
    {
        $qb = $this->createQueryBuilder('version_build');
        $qb->select('*');
        $this->load($qb);

        return $qb;
    }
}
