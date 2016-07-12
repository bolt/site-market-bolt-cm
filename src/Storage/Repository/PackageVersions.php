<?php

namespace Bolt\Extension\Bolt\MarketPlace\Storage\Repository;

use Bolt\Extension\Bolt\MarketPlace\Storage\Entity;
use Bolt\Storage\Repository;

/**
 * Package version repository.
 *
 * @author Gawain Lynch <gawain.lynch@gmail.com>
 */
class PackageVersions extends AbstractRepository
{
    /**
     * {@inheritdoc}
     */
    public function createQueryBuilder($alias = null)
    {
        return $this->em->createQueryBuilder()
            ->from($this->getTableName(), 'package_versions');
    }
}
