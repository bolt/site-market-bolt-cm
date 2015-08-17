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
    
    public function search($keyword, $type = null)
    {
        $packages = $this->createQueryBuilder('p')
                ->where('p.approved = :status')
                ->andWhere('p.name LIKE :search OR p.title LIKE :search OR p.keywords LIKE :search');
        
        if ($type !== null) {
            $packages->andWhere('p.type = :type');
            $packages->setParameter('type', $type);
        }
        
        $results = $packages->setParameter('status', true)
                ->setParameter('search', "%".$keyword."%")
                ->getQuery()
                ->getResult();
                
        return $results;
    }
    
    public function fetchTags()
    {
        $packages = $this->createQueryBuilder('p')
            ->select('p.keywords')
            ->where('p.approved = true')
            ->getQuery()
            ->getResult();
        return $packages;
    }
    
    public function popularTags()
    {
        $allTags = $this->fetchTags();
        $tagList = [];
        foreach($allTags as $tag) {
            $tagList = array_merge($tagList, explode(",", $tag['keywords']) );
        }
        $tagList = array_filter($tagList);
        $tagList = array_count_values($tagList);

        // sort on the value (word count) in descending order
        arsort($tagList);

        // get the top frequent words
        return array_slice($tagList, 0, 10);
    }

}