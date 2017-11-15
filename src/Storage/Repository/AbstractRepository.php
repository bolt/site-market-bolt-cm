<?php

namespace Bundle\Site\MarketPlace\Storage\Repository;

use Bolt\Storage\QuerySet;
use Bolt\Storage\Repository;
use Ramsey\Uuid\Uuid;

/**
 * Base repository for the Market Place.
 *
 * @author Gawain Lynch <gawain.lynch@gmail.com>:
 */
abstract class AbstractRepository extends Repository
{
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

        try {
            $result = $querySet->execute();
        } catch (\PDOException $e) {
            // Hackishly handle "Object not in prerequisite state: 7 ERROR: currval of sequence "bolt_market_stat_id_seq" is not yet defined in this session"
            if ((int) $e->getCode() !== 55000) {
                throw $e;
            }

            return false;
        }

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

    /**
     * Get a column count query result.
     *
     * @param array|false $result
     *
     * @return integer|false
     */
    protected function getCount($result)
    {
        if ($result !== false && isset($result['count'])) {
            return $result['count'];
        }

        return false;
    }
}
