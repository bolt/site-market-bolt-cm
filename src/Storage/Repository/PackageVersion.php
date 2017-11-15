<?php

namespace Bundle\Site\MarketPlace\Storage\Repository;

use Bundle\Site\MarketPlace\Storage\Entity;
use Composer\Semver\Constraint\Constraint;
use Composer\Semver\VersionParser;

/**
 * Package version repository.
 *
 * @author Gawain Lynch <gawain.lynch@gmail.com>
 */
class PackageVersion extends AbstractRepository
{
    /**
     * @param string $packageId
     * @param string $version
     *
     * @return Entity\PackageVersion|bool
     */
    public function getPackageVersion($packageId, $version)
    {
        $query = $this->getPackageVersionQuery($packageId, $version);

        return $this->findOneWith($query);
    }

    public function getPackageVersionQuery($packageId, $version)
    {
        $qb = $this->createQueryBuilder('v');
        $qb
            ->select('*')
            ->where('v.package_id = :package_id')
            ->andWhere('v.version = :version')
            ->setParameter('package_id', $packageId)
            ->setParameter('version', $version)
        ;

        return $qb;
    }
    /**
     * @param string $packageId
     *
     * @return Entity\PackageVersion[]
     */
    public function getPackageVersions($packageId)
    {
        $query = $this->getPackageVersionsQuery($packageId);

        return $this->findWith($query);
    }

    public function getPackageVersionsQuery($packageId)
    {
        $qb = $this->createQueryBuilder('v');
        $qb
            ->select('*')
            ->where('v.package_id = :package_id')
            ->setParameter('package_id', $packageId)
        ;

        return $qb;
    }

    /**
     * @param string $packageId
     * @param string $stability
     *
     * @return Entity\PackageVersion|bool
     */
    public function getLatestReleaseForStability($packageId, $stability)
    {
        $query = $this->getLatestReleaseForStabilityQuery($packageId, $stability);

        return $this->findOneWith($query);
    }

    public function getLatestReleaseForStabilityQuery($packageId, $stability)
    {
        $qb = $this->createQueryBuilder('v');
        $qb
            ->select('*')
            ->where('v.package_id = :package_id')
            ->andWhere('v.stability = :stability')
            ->orderBy('v.updated', 'DESC')
            ->setParameter('package_id', $packageId)
            ->setParameter('stability', $stability)
        ;

        return $qb;
    }

    /**
     * @param string $packageId
     * @param string $stability
     * @param string $boltVersion
     *
     * @return Entity\PackageVersion|bool
     */
    public function getLatestCompatibleVersion($packageId, $stability, $boltVersion)
    {
        $query = $this->LatestCompatibleVersionsQuery($packageId, $stability);

        $versionEntities = $this->findWith($query);
        if ($versionEntities === false) {
            return false;
        }

        $parser = new VersionParser();
        $boltVersion = new Constraint('==', $parser->normalize($boltVersion));

        /** @var Entity\PackageVersion $versionEntity */
        foreach ($versionEntities as $versionEntity) {
            if ($versionEntity->getBoltMin() === null || $versionEntity->getBoltMax() === null) {
                continue;
            }
            $boltConstraints = $parser->parseConstraints(sprintf('%s,%s', $versionEntity->getBoltMin(), $versionEntity->getBoltMax()));
            if ($boltConstraints->matches($boltVersion)) {
                return $versionEntity;
            }
        }

        return false;
    }

    /**
     * @param string $packageId
     * @param string $stability
     * @param string $boltVersion
     *
     * @return Entity\PackageVersion[]
     */
    public function getLatestCompatibleVersions($packageId, $stability, $boltVersion)
    {
        $query = $this->LatestCompatibleVersionsQuery($packageId, $stability);

        $versionEntities = $this->findWith($query);
        $parser = new VersionParser();
        $boltVersion = new Constraint('==', $parser->normalize($boltVersion));

        /** @var Entity\PackageVersion $versionEntity */
        foreach ($versionEntities as $key => $versionEntity) {
            $boltConstraints = $parser->parseConstraints(sprintf('%s,%s', $versionEntity->getBoltMin(), $versionEntity->getBoltMax()));
            if (!$boltConstraints->matches($boltVersion)) {
                unset($versionEntities[$key]);
            }
        }

        return $versionEntities;
    }

    public function LatestCompatibleVersionsQuery($packageId, $stability)
    {
        $qb = $this->createQueryBuilder('v');
        $qb
            ->select('*')
            ->where('v.package_id = :package_id')
            ->andWhere('v.stability = :stability')
            ->orderBy('v.version', 'DESC')
            ->setParameter('package_id', $packageId)
            ->setParameter('stability', $stability)
        ;

        return $qb;
    }
}
