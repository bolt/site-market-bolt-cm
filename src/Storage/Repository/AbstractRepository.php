<?php

namespace Bolt\Extension\Bolt\MarketPlace\Storage\Repository;

use Bolt\Storage\QuerySet;
use Bolt\Storage\Repository;
use Ramsey\Uuid\Uuid;

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

    /**
     * {@inheritdoc}
     */
    public function insert($entity)
    {
        $entity->setId(Uuid::uuid4()->toString());
        $querySet = new QuerySet();
        $qb = $this->em->createQueryBuilder();
        $qb->insert($this->getTableName());
        $querySet->append($qb);
        $this->persist($querySet, $entity, []);

        $result = $querySet->execute();

        // Try and set the entity id using the response from the insert
        try {
            $entity->setId($querySet->getInsertId());
        } catch (\Exception $e) {
        }

        return $result;
    }

    /**
     * {@inheritdoc}
     */
    protected function getLoadQuery()
    {
        $parts = explode('\\', $this->entityName);
        $qb = $this->createQueryBuilder(strtolower(end($parts)));
        $qb->select('*');
        $this->load($qb);

        return $qb;
    }
}
