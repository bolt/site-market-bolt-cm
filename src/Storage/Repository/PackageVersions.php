<?php

namespace Bolt\Extension\Bolt\MarketPlace\Storage\Repository;

use Bolt\Extension\Bolt\MarketPlace\Storage\Entity;
use Composer\Semver\Constraint\Constraint;
use Composer\Semver\VersionParser;

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
        // Gotta fix this alias thing …
        // HINT: The abstract overrides getLoadQuery() for a reason I can't quite remember
        return $this->em->createQueryBuilder()
            ->from($this->getTableName(), $alias === 'packageversions' ? 'package_versions' :$alias);
    }

    /**
     * @param string $packageId
     * @param string $stability
     *
     * @return array
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
     * @return Entity\PackageVersions|bool
     */
    public function getLatestCompatibleVersion($packageId, $stability, $boltVersion)
    {
        $query = $this->getLatestVersionQuery($packageId, $stability);

        $versionEntities = $this->findWith($query);
        if ($versionEntities === false) {
            return false;
        }

        $parser = new VersionParser();
        $boltVersion = new Constraint('==', $parser->normalize($boltVersion));

        /** @var Entity\PackageVersions $versionEntity */
        foreach ($versionEntities as $versionEntity) {
            $boltConstraints = $parser->parseConstraints(sprintf('%s,%s', $versionEntity->getBoltMin(), $versionEntity->getBoltMax()));
            if ($boltConstraints->matches($boltVersion)) {
                return $versionEntity;
            }
        }


        return false;
    }

    public function getLatestVersionQuery($packageId, $stability)
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
