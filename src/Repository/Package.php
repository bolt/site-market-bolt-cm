<?php
namespace Bolt\Extensions\Repository;

use Doctrine\ORM\EntityRepository;

class Package extends EntityRepository
{

    public function mostDownloaded($limit = 10)
    {
        return $this->statCount('install', $limit);
    }
    
    public function mostStarred($limit = 10)
    {
        return $this->statCount('star', $limit);
    }
    
    protected function statCount($type, $limit)
    {
        $qb = $this->createQueryBuilder("p");
        $qb->select("p, count(p.id) as hidden pcount")
            ->innerJoin("p.stats", "s")
            ->where('s.type = :type')
            ->andWhere('p.approved = true')
            ->groupBy('p.id')
            ->orderBy('pcount',"DESC")
            ->setParameter('type', $type)
            ->setMaxResults($limit);
        return $qb->getQuery()->getResult();
    }

}