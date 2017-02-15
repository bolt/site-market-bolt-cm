<?php

namespace Bolt\Extension\Bolt\MarketPlace\Storage;

use Bolt\Storage\EntityManager;
use Composer\Package\PackageInterface;

/**
 * Version data handler.
 *
 * @author Gawain Lynch <gawain.lynch@gmail.com>
 */
class VersionDataHandler
{
    /**
     * @param EntityManager $em
     * @param array         $packages
     */
    public function updateVersionEntities(EntityManager $em, array $packages)
    {
        /** @var Repository\PackageVersion $repo */
        $repo = $em->getRepository(Entity\PackageVersion::class);
        $packageData = $this->getPackageData($packages);
        foreach ($packageData as $name => $versionData) {
            /** @var Entity\Package $packageEntity */
            $packageEntity = $em->getRepository(Entity\Package::class)->findOneBy(['name' => $name]);
            if ($packageEntity === false) {
                continue;
            }

            // Check stored version entities and copy primary key
            $dbVersionEntities = $repo->getPackageVersions($packageEntity->getId());
            /** @var Entity\PackageVersion $dbVersionEntity */
            foreach ($dbVersionEntities as $dbVersionEntity) {
                $versionNumber = $dbVersionEntity->getVersion();
                $versionEntities = $packageData[$name];
                if (!isset($versionEntities[$versionNumber])) {
                    // Version stored in the database no longer exists in the repository
                    $repo->delete($dbVersionEntity);
                    continue;
                }
                /** @var Entity\PackageVersion $versionEntity */
                $versionEntity = $versionEntities[$versionNumber];
                $versionEntity->setId($dbVersionEntity->getId());
            }

            /** @var Entity\PackageVersion $entity */
            foreach ($versionData as $entity) {
                $entity->setPackageId($packageEntity->getId());
                $repo->save($entity);
            }
        }
    }

    /**
     * @param PackageInterface[] $packages
     *
     * @return array
     */
    private function getPackageData($packages)
    {
        $data = [];
        foreach ($packages as $package) {
            $name = $package->getPrettyName();
            $version = $package->getVersion();
            $requires = $this->getBoltRequire($package->getRequires());

            $entity = new Entity\PackageVersion();
            $entity->setStability($package->getStability());
            $entity->setVersion($version);
            $entity->setUpdated($package->getReleaseDate());
            $entity->setPrettyVersion($package->getPrettyVersion());
            $entity->setBoltMin($requires['min']);
            $entity->setBoltMax($requires['max']);

            $data[$name][$version] = $entity;
        }

        return $data;
    }

    /**
     * @param array $requires
     *
     * @return array|null
     */
    private function getBoltRequire(array $requires)
    {
        if (!isset($requires['bolt/bolt'])) {
            return null;
        }

        /** @var \Composer\Package\Link $require */
        $require = $requires['bolt/bolt'];
        /** @var \Composer\Semver\Constraint\MultiConstraint $constraints */
        $constraints = $require->getConstraint();
        /** @var \Composer\Semver\Constraint\Constraint[] $pair*/
        $pair = $constraints->getConstraints();

        return [
            'min' => $pair[0]->getPrettyString(),
            'max' => $pair[1]->getPrettyString(),
        ];
    }
}
