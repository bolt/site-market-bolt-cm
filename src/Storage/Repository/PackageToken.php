<?php

namespace Bolt\Extension\Bolt\MarketPlace\Storage\Repository;

use Bolt\Extension\Bolt\MarketPlace\Storage\Entity;
use Doctrine\DBAL\Query\QueryBuilder;

/**
 * Package token repository.
 *
 * @author Gawain Lynch <gawain.lynch@gmail.com>
 */
class PackageToken extends AbstractRepository
{
    /**
     * @param string $packageId
     * @param string $type
     *
     * @return Entity\Package|false
     */
    public function getToken($packageId, $type)
    {
        $qb = $this->getTokenQuery($packageId, $type);

        return $this->findOneWith($qb);
    }

    public function getTokenQuery($packageId, $type)
    {
        /** @var QueryBuilder $qb */
        $qb = $this->createQueryBuilder('t');
        $qb
            ->select('*')
            ->where('t.package_id = :packageId')
            ->andWhere('t.type = :type')
            ->setParameter('packageId', $packageId)
            ->setParameter('type', $type)
        ;

        return $qb;
    }

    /**
     * @param string $packageId
     * @param string $type
     *
     * @return Entity\PackageToken|false
     */
    public function getValidPackageToken($packageId, $type)
    {
        $tokenEntity = $this->getToken($packageId, $type);

        if ($tokenEntity === false) {
            $tokenEntity = $this->getEntityBuilder()->create(['package_id' => $packageId, 'type' => $type]);
            $tokenEntity->regenerateToken();

            $this->save($tokenEntity);
        }
        if ($tokenEntity->getToken() === null) {
            $tokenEntity->regenerateToken();
            $this->save($tokenEntity);
        }

        return $tokenEntity;
    }
}
