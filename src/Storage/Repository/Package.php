<?php

namespace Bolt\Extension\Bolt\MarketPlace\Storage\Repository;

use Bolt\Extension\Bolt\MarketPlace\Storage\Entity;
use Doctrine\DBAL\Query\QueryBuilder;

/**
 * Package repository.
 *
 * @author Ross Riley <riley.ross@gmail.com>
 * @author Gawain Lynch <gawain.lynch@gmail.com>
 */
class Package extends AbstractRepository
{
    /**
     * @return array
     */
    public function getPopularTags()
    {
        $allTags = $this->getTags();
        $tagList = [];
        foreach ($allTags as $tag) {
            $tagList = array_merge($tagList, (array) $tag['keywords']);
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
     * @param string $action
     * @param string $composerType
     * @param int    $limit
     *
     * @return Entity\Package[]|false
     */
    public function getInstallStatistics($action, $composerType, $limit)
    {
        $query = $this->getInstallStatisticsQuery($action, $composerType, $limit);

        return $this->findWith($query);
    }

    public function getInstallStatisticsQuery($action, $composerType, $limit)
    {
        $installStatTable = 'bolt_marketplace_stat_install';
        /** @var QueryBuilder $qb */
        $qb = $this->createQueryBuilder('p')
            ->select('p.*, count(p.id) AS pcount')
            ->addSelect('p.source as source')
            ->innerJoin('p', $installStatTable, 's', 'p.id = s.package_id')
            ->where('p.type = :composerType')
            ->andWhere('s.type = :action')
            ->andWhere('p.approved = true')
            ->groupBy('p.id')
            ->orderBy('pcount', 'DESC')
            ->setParameter('composerType', $composerType)
            ->setParameter('action', $action)
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
        /** @var QueryBuilder $qb */
        $qb = $this->createQueryBuilder('p')
            ->where('p.approved = :status')
        ;

        if ($keyword !== null) {
            $andCond = $qb->expr()
                ->orX()
                ->add($qb->expr()->like('lower(p.name)', ':search'))
                ->add($qb->expr()->like('lower(p.title)', ':search'))
                ->add($qb->expr()->orX("(p.keywords #>> '{}' ILIKE :search)"))
                ->add($qb->expr()->orX("(p.authors #>> '{}' ILIKE :search)"))
            ;
            $qb->andWhere($andCond)
                ->setParameter('search', '%' . strtolower($keyword) . '%')
            ;
        }

        if ($type !== null) {
            $qb->andWhere('p.type = :type');
            $qb->setParameter('type', $type);
        }

        if ($limit !== null) {
            $qb->setMaxResults($limit);
        }

        $qb->groupBy('p.id');

        switch ($order) {
            case 'date':
                $qb
                    ->select('DISTINCT ON (p.id, p.created) p.*')
                    ->orderBy('p.created', 'DESC')
                ;
                break;
            case 'modified':
// broken
                $qb
                    ->select('DISTINCT ON (p.id, p.updated) p.*')
                    ->orderBy('p.updated', 'DESC')
                ;
                break;
            case 'name':
                $qb
                    ->select('DISTINCT ON (p.id, p.title) p.*')
                    ->orderBy('p.title', 'ASC');
                break;
            case 'downloads':
                $installStatTable = 'bolt_marketplace_stat_install';
                $qb
                    ->select('DISTINCT ON (p.id, pcount) p.*, COUNT(p.id) as pcount')
                    ->leftJoin('p', $installStatTable, 's', 'p.id = s.package_id')
                    ->groupBy('p.id')
                    ->orderBy('pcount', 'DESC')
                ;
                break;
            case 'stars':
                $installStarTable = 'bolt_marketplace_package_star';
                $qb
                    ->select('DISTINCT ON (p.id, pcount) p.*, COUNT(p.id) as pcount')
                    ->leftJoin('p', $installStarTable, 's', 'p.id = s.package_id')
                    ->groupBy('p.id')
                    ->orderBy('pcount', 'DESC')
                ;
                break;

            default:
                $qb->select('DISTINCT ON (p.id) p.*');
                break;
        }

        $qb->setParameter('status', true);

        return $qb;
    }

    /**
     * Temporary! Both search functions need overhaul.
     *
     * @param string $keyword
     * @param string $type
     * @param int    $boltMajor
     * @param string $order
     * @param int    $limit
     *
     * @return array
     */
    public function searchByVersion($keyword, $type = null, $boltMajor, $order = null, $limit = null)
    {
        $query = $this->searchByVersionQuery($keyword, $type, $boltMajor, $order, $limit);

        return $this->findWith($query);
    }

    public function searchByVersionQuery($keyword, $type, $boltMajor, $order, $limit)
    {
        /** @var QueryBuilder $qb */
        $qb = $this->createQueryBuilder('p')
            ->leftJoin('p', 'bolt_marketplace_package_versions', 'v', 'p.id = v.package_id')
            ->where('p.approved = :status')
        ;

        $qb->andWhere($qb->expr()->like('v.bolt_min', $qb->expr()->literal('>= ' . $boltMajor . '%')));

        if ($keyword !== null) {
            $andCond = $qb->expr()
                ->orX()
                ->add($qb->expr()->like('lower(p.name)', ':search'))
                ->add($qb->expr()->like('lower(p.title)', ':search'))
                ->add($qb->expr()->orX("(p.keywords #>> '{}' ILIKE :search)"))
                ->add($qb->expr()->orX("(p.authors #>> '{}' ILIKE :search)"))
            ;
            $qb->andWhere($andCond)
                ->setParameter('search', '%' . strtolower($keyword) . '%')
            ;
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
                $qb
                    ->select('DISTINCT ON (p.id, p.created) p.*')
                    ->orderBy('p.created', 'DESC')
                ;
                break;
            case 'modified':
                $qb->orderBy('p.updated', 'DESC');
                break;
            case 'name':
                $qb
                    ->select('DISTINCT ON (p.id, p.title) p.*')
                    ->orderBy('p.title', 'ASC');
                break;
            default:
                $qb->select('DISTINCT ON (p.id) p.*');
                break;
        }

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
        /** @var QueryBuilder $qb */
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
        /** @var QueryBuilder $qb */
        $qb = $this->createQueryBuilder('p');
        $qb
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
        /** @var QueryBuilder $qb */
        $qb = $this->createQueryBuilder('p');
        $qb
            ->select('*')
            ->where('p.approved = :approved')
            ->andWhere('lower(p.name) LIKE :author')
            ->setParameter('approved', true)
            ->setParameter('author', strtolower($author) . '/%')
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
     * @param string $accountId
     *
     * @return array
     */
    public function getStarredPackages($accountId)
    {
        $query = $this->getStarredPackagesQuery($accountId);

        return $this->findWith($query);
    }

    public function getStarredPackagesQuery($accountId)
    {
        $installStarTable = 'bolt_marketplace_package_star';
        /** @var QueryBuilder $qb */
        $qb = $this->createQueryBuilder('p')
            ->innerJoin('p', $installStarTable, 's', 'p.id = s.package_id')
            ->select('p.*')
            ->where('p.approved = :approved')
            ->setParameter('approved', true)
            ->andWhere('s.account_id = :account_id')
            ->setParameter('account_id', $accountId)
        ;

        return $qb;
    }
}
