<?php

namespace Bolt\Extension\Bolt\MarketPlace\Storage\Repository;

use Bolt\Extension\Bolt\MarketPlace\Storage\Entity;
use Bolt\Storage\Repository;

/**
 * Package repository.
 *
 * @author Ross Riley <riley.ross@gmail.com>
 * @author Gawain Lynch <gawain.lynch@gmail.com>
 */
class Package extends AbstractRepository
{
    /**
     * @param int $limit
     *
     * @return Entity\Package[]|false
     */
    public function getMostDownloadedStats($limit = 10)
    {
        return $this->getStatCount('install', $limit);
    }

    /**
     * @param int $limit
     *
     * @return Entity\Package[]|false
     */
    public function getMostStarredStats($limit = 10)
    {
        return $this->getStatCount('star', $limit);
    }

    /**
     * @return array
     */
    public function getPopularTags()
    {
        $allTags = $this->getTags();
        $tagList = [];
        foreach ($allTags as $tag) {
            $tagList = array_merge($tagList, explode(',', $tag['keywords']));
        }
        $tagList = array_filter($tagList);
        $tagList = array_diff($tagList, ['bolt']);
        $tagList = array_count_values($tagList);

        // sort on the value (word count) in descending order
        arsort($tagList);

        // get the top frequent words
        return array_slice($tagList, 0, 10);
    }

    /**
     * @param string  $type
     * @param integer $limit
     *
     * @return Entity\Package[]|false
     */
    public function getStatCount($type, $limit)
    {
        $query = $this->getStatCountQuery($type, $limit);

        return $this->findWith($query);
    }

    public function getStatCountQuery($type, $limit)
    {
        $qb = $this->createQueryBuilder('p');
        $qb
            //->select('count(p.id) AS HIDDEN pcount')
            ->select('count(p.id) AS pcount')
            ->innerJoin('p', 'bolt_marketplace_stat', 's', 'p.id = s.package_id')
            ->where('s.type = :type')
            ->andWhere('p.approved = true')
            ->groupBy('p.id')
            ->orderBy('pcount', 'DESC')
            ->setParameter('type', $type)
            ->setMaxResults($limit)
        ;

        return $qb;
    }

    /**
     * @param string $keyword
     * @param string $type
     * @param string $order
     * @param int    $limit
     *
     * @return Entity\Package[]|false
     */
    public function search($keyword, $type = null, $order = null, $limit = null)
    {
        $query = $this->getSearchQuery($keyword, $type, $order, $limit);

        return $this->findWith($query);
    }

    public function getSearchQuery($keyword, $type, $order, $limit)
    {
        $qb = $this->createQueryBuilder('p')
            //->select('count(p.id) AS HIDDEN pcount')
            ->select('*')
            ->addSelect('p.id as id')
            ->addSelect('p.account_id as account_id')
            ->addSelect('p.source as source')
            ->addSelect('count(p.id) AS pcount')
            ->leftJoin('p', 'bolt_marketplace_stat', 's', 'p.id = s.package_id')
            ->where('p.approved = :status')
        ;

        if ($keyword !== null) {
            $qb->andWhere('p.name LIKE :search OR p.title LIKE :search OR p.keywords LIKE :search OR p.authors LIKE :search');
            $qb->setParameter('search', '%' . $keyword . '%');
        }

        if ($type !== null) {
            $qb->andWhere('p.type = :type');
            $qb->setParameter('type', $type);
        }

        if ($limit !== null) {
            $qb->setMaxResults($limit);
        }

        switch ($order) {
            case 'date':
                $qb->orderBy('p.created', 'DESC');
                break;
            case 'modified':
                $qb->orderBy('p.updated', 'DESC');
                break;
            case 'name':
                $qb->orderBy('p.title', 'ASC');
                break;
            case 'downloads':
                $qb->andWhere("s.type = 'install'");
                $qb->orderBy('pcount', 'DESC');
                break;
            case 'stars':
                $qb->andWhere("s.type = 'star'");
                $qb->orderBy('pcount', 'DESC');
                break;

            default:
                break;
        }

        $qb->groupBy('p.id');
        $qb->setParameter('status', true);

        return $qb;
    }

    /**
     * @return Entity\Package[]|false
     */
    public function getTags()
    {
        $query = $this->getTagsQuery();

        return $this->findWith($query);
    }

    public function getTagsQuery()
    {
        $qb = $this->createQueryBuilder('p')
            ->select('p.keywords')
            ->where('p.approved = true')
        ;

        return $qb;
    }

    /**
     * @param int    $limit
     * @param string $type
     *
     * @return Entity\Package[]|false
     */
    public function getLatest($limit = 10, $type = null)
    {
        $query = $this->getLatestQuery($limit, $type);

        return $this->findWith($query);
    }

    public function getLatestQuery($limit, $type)
    {
        $qb = $this->createQueryBuilder('p')
            ->select('*')
            ->where('p.approved = :approved')
            ->setParameter('approved', true)
            ->orderBy('created', 'DESC')
            ->setMaxResults($limit)
        ;

        if ($type !== null) {
            $qb
                ->andWhere('p.type = :type')
                ->setParameter('type', $type)
            ;
        }

        return $qb;
    }

    /**
     * @param string $author
     * @param int    $limit
     * @param string $type
     *
     * @return Entity\Package[]|false
     */
    public function getAllByComposerAuthor($author, $limit = null, $type = null)
    {
        $query = $this->getAllByComposerAuthorQuery($author, $limit, $type);

        return $this->findWith($query);
    }

    public function getAllByComposerAuthorQuery($author, $limit, $type)
    {
        $qb = $this->createQueryBuilder('p')
            ->select('*')
            ->where('p.approved = :approved')
            ->andWhere('p.name LIKE :author')
            ->setParameter('approved', true)
            ->setParameter('author', $author . '/%')
            ->orderBy('created', 'DESC')
            ->setMaxResults($limit)
        ;

        if ($type !== null) {
            $qb
                ->andWhere('p.type = :type')
                ->setParameter('type', $type)
            ;
        }

        return $qb;
    }
}
