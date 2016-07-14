<?php

namespace Bolt\Extension\Bolt\MarketPlace\Service;

use Bolt\Extension\Bolt\MarketPlace\Storage\Entity;
use Bolt\Extension\Bolt\MarketPlace\Storage\Repository;
use Bolt\Extension\Bolt\MarketPlace\Storage\VersionDataHandler;
use Bolt\Storage\EntityManager;
use Composer\Config;
use Composer\IO\BufferIO;
use Composer\IO\IOInterface;
use Composer\Json\JsonFile;
use Composer\Package\CompletePackage;
use Composer\Package\Dumper\ArrayDumper;
use Composer\Package\Link;
use Composer\Package\PackageInterface;
use Composer\Repository\Vcs\VcsDriverInterface;
use Composer\Repository\VcsRepository;
use Composer\Semver\Constraint\Constraint;
use Composer\Util\RemoteFilesystem;

/**
 * Composer package manager class.
 *
 * @author Ross Riley <riley.ross@gmail.com>
 */
class PackageManager
{
    /** @var IOInterface */
    protected $io;
    /** @var Config */
    protected $config;

    /**
     * Constructor.
     *
     * @param Config $config
     */
    public function __construct(Config $config)
    {
        $this->config = $config;
    }

    /**
     * @return IOInterface
     */
    public function getIo()
    {
        if ($this->io === null) {
            $this->io = new BufferIO();
        }

        return $this->io;
    }

    /**
     * @param IOInterface $io
     */
    public function setIo(IOInterface $io)
    {
        $this->io = $io;
    }

    /**
     * @param Entity\Package $package
     *
     * @return Entity\Package
     */
    public function syncPackage(Entity\Package $package)
    {
        $this->updatePackageInformation($package);
    }

    /**
     * @param Entity\Package          $package
     * @param VcsDriverInterface|null $identifier
     *
     * @return array|false
     */
    public function loadInformation(Entity\Package $package, VcsDriverInterface $identifier = null)
    {
        $repository = $this->loadRepository($package);
        $driver = $repository->getDriver();
        if ($driver === null) {
            return false;
        }

        if ($identifier === null) {
            $identifier = $driver->getRootIdentifier();
        }

        return $driver->getComposerInformation($identifier);
    }

    /**
     * @param Entity\Package $package
     *
     * @return VcsRepository
     */
    public function loadRepository(Entity\Package $package)
    {
        $io = $this->getIo();
        $io->loadConfiguration($this->config);
        $repository = new VcsRepository(['url' => $package->getRawSource()], $io, $this->config);

        return $repository;
    }

    /**
     * @param Entity\Package $package
     *
     * @return array
     */
    public function getVersions(Entity\Package $package)
    {
        $info = [];
        $rep = $this->loadRepository($package);
        $versions = $rep->getPackages();
        foreach ($versions as $version) {
            $info[] = $version;
        }

        return $info;
    }

    /**
     * @param Entity\Package $package
     * @param string         $boltVersion
     * @param string         $token
     *
     * @return array
     */
    public function getInfo(Entity\Package $package, $boltVersion, $token = null)
    {
        $info = [];
        $repo = $this->loadRepository($package);
        $versions = $repo->getPackages();
        $dumper = new ArrayDumper();
        $releaseInfo = $this->getReleaseInfo($package, $token);

        foreach ($versions as $version) {
            if (!$boltVersion || $this->isCompatible($version, $boltVersion)) {
                $data = $dumper->dump($version);
                $data['stability'] = $version->getStability();

                if ($releaseInfo) {
                    foreach ($releaseInfo as $rel) {
                        if ($version->getPrettyVersion() === $rel['tag_name']) {
                            $data['release'] = $rel;
                        }
                    }
                }

                $info[] = $data;
            }
        }

        return $info;
    }

    /**
     * If we have a GitHub repo this gets some extra information about the version
     *
     * @param Entity\Package $package
     * @param string         $token
     *
     * @return array
     */
    public function getReleaseInfo(Entity\Package $package, $token = null)
    {
        $io = $this->getIo();
        $io->loadConfiguration($this->config);
        $rfs = new RemoteFilesystem($io, $this->config);

        try {
            $baseApiUrl = sprintf(
                'https://api.github.com/repos/%s/releases?access_token=%s',
                str_replace('https://github.com/', '', $package->getSource()),
                $token
            );
            $repoData = JsonFile::parseJson($rfs->getContents('github.com', $baseApiUrl, false), $baseApiUrl);
        } catch (\Exception $e) {
            return [];
        }

        return $repoData;
    }

    /**
     * If we have a GitHub repo this gets the readme content for the package
     *
     * @param Entity\Package|string $package
     *
     * @return array
     */
    public function getReadme(Entity\Package $package)
    {
        $io = $this->getIo();
        $io->loadConfiguration($this->config);
        $rfs = new RemoteFilesystem($io, $this->config);

        try {
            $baseApiUrl = 'https://api.github.com/repos/' . str_replace('https://github.com/', '', $package->getSource()) . '/readme';
            $readme = JsonFile::parseJson($rfs->getContents('github.com', $baseApiUrl, false), $baseApiUrl);
        } catch (\Exception $e) {
            return [];
        }

        return $readme;
    }

    /**
     * @param PackageInterface $version
     * @param string           $boltVersion
     *
     * @return bool
     */
    public function isCompatible(PackageInterface $version, $boltVersion)
    {
        /** @var Link $require */
        $require = $version->getRequires();
        if (!isset($require['bolt/bolt'])) {
            return false;
        }
        $constraint = $require['bolt/bolt']->getConstraint();
        $v = new Constraint('=', $boltVersion . '.0');

        return $constraint->matches($v);
    }

    /**
     * @param Entity\Package $package
     * @param bool           $isAdmin
     *
     * @throws \InvalidArgumentException
     */
    public function validate(Entity\Package $package, $isAdmin = false)
    {
        $valid = true;
        $errors = [];
        $manifest = $this->loadInformation($package);

        if ($manifest === false) {
            $valid = false;
            $errors[] = 'The repository URL you provided could not be loaded - Check that it points to a publicly readable repository.';
        } else {
            if (!isset($manifest['name']) || !preg_match('#^[a-z0-9]+/[a-z0-9\-]+#', $manifest['name'])) {
                $valid = false;
                $errors[] = "'name' in composer.json must be set, must be lowercase and contain only alphanumerics";
            }

            if (!isset($manifest['type']) ||  !preg_match('#^bolt-(theme|extension)#', $manifest['type'])) {
                $valid = false;
                $errors[] = "'type' in composer.json must be set, and must be either 'bolt-extension' or 'bolt-theme'";
            }

            if (!isset($manifest['require'])) {
                $valid = false;
                $errors[] = "'require' in composer.json must be set, and must provide Bolt version compatibility";
            }

            if (isset($manifest['name']) && substr($manifest['name'], 0, 5) === 'bolt/') {
                if (!$isAdmin) {
                    $valid = false;
                    $errors[] = "package name uses a 'bolt/' prefix which is reserved for official extensions only.";
                }
            }
        }

        if ($valid === false) {
            throw new \InvalidArgumentException(join("\n\n", $errors));
        }
    }

    /**
     * Update package and version entities
     *
     * @param EntityManager      $em
     * @param PackageInterface[] $packages
     */
    public function updateEntities(EntityManager $em, array $packages)
    {
        $complete = [];
        $updated = [];
        $current = null;

        /** @var Repository\Package $repo */
        $repo = $em->getRepository(Entity\Package::class);
        foreach ($packages as $key => $package) {
            $name = $package->getName();
            $stability = $package->getStability();
            $version = $package->getVersion();

            if ($current === null) {
                $current = $name;
                $updated = [];
            } elseif ($current !== $name) {
                if ($this->updateEntity($repo, $package, $updated) === false) {
                    // Unset the package from the array so we don't send it to the
                    // version data handler
                    unset($packages[$key]);
                }
                $current = null;
            }
            $updated[$stability][$version] = [
                'version'  => $package->getPrettyVersion(),
                'released' => $package->getReleaseDate(),
            ];

            if (isset($complete[$name])) {
                continue;
            }
            if ($stability === 'stable') {
                $complete[$name] = true;
            }
        }

        // Update stored local versions
        (new VersionDataHandler())->updateVersionEntities($em, $packages);
    }

    /**
     * Check to see if the Composer package is one we manage.
     *
     * @param Repository\Package $repo
     * @param PackageInterface   $package
     * @param array              $updated
     *
     * @return bool
     */
    protected function updateEntity(Repository\Package $repo, PackageInterface $package, array $updated)
    {
        /** @var Entity\Package $packageEntity */
        $packageEntity = $repo->findOneBy(['name' => $package->getPrettyName()]);
        if ($packageEntity === false) {
            return false;
        }
        $this->updatePackageInformation($packageEntity, $package, $updated);

        $repo->save($packageEntity);

        return true;
    }

    /**
     * Update a package entity with information from Composer.
     *
     * @param Entity\Package       $packageEntity
     * @param CompletePackage|null $package
     * @param array                $updated
     */
    protected function updatePackageInformation(Entity\Package $packageEntity, CompletePackage $package = null, array $updated = [])
    {
        $authors = [];
        if ($package === null) {
            $information = $this->loadInformation($packageEntity);
            $packageEntity->setName($information['name']);
            if (isset($information['type'])) {
                $packageEntity->setType($information['type']);
            }
            if (isset($information['keywords'])) {
                $packageEntity->setKeywords($information['keywords']);
            }
            if (isset($information['authors'])) {
                $authors = [];
                foreach ($information['authors'] as $author) {
                    $authors[] = $author['name'];
                }
            }
            if (isset($information['support'])) {
                $packageEntity->setSupport($information['support']);
            }

            if (isset($information['suggest'])) {
                $packageEntity->setSuggested($information['suggest']);
            }

            if (isset($information['extra']) && isset($information['extra']['bolt-screenshots'])) {
                $packageEntity->setScreenshots($information['extra']['bolt-screenshots']);
            }

            if (isset($information['extra']) && isset($information['extra']['bolt-icon'])) {
                $packageEntity->setIcon($information['extra']['bolt-icon']);
            }
        } else {
            /** @var \Composer\Package\CompletePackage $package */
            $packageEntity->setName($package->getName());
            $packageEntity->setType($package->getType());
            $packageEntity->setKeywords((array) $package->getKeywords());
            foreach ((array) $package->getAuthors() as $author) {
                $authors[] = $author['name'];
            }
            $packageEntity->setSupport((array) $package->getSupport());
            $packageEntity->setSuggested((array) $package->getSuggests());
            $extra = $package->getExtra();
            if (isset($extra['bolt-screenshots'])) {
                $packageEntity->setScreenshots((array) $extra['bolt-screenshots']);
            }
            if (isset($extra['bolt-icon'])) {
                $packageEntity->setIcon($extra['bolt-icon']);
            }
            $packageEntity->setUpdated($updated);
        }

        $packageEntity->setAuthors($authors);
    }
}
