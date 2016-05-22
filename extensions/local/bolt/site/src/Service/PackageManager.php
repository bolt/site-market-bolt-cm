<?php

namespace Bolt\Extension\Bolt\MarketPlace\Service;

use Bolt\Extension\Bolt\MarketPlace\Storage\Entity;
use Composer\Config;
use Composer\IO\NullIO;
use Composer\Json\JsonFile;
use Composer\Package\Dumper\ArrayDumper;
use Composer\Package\Link;
use Composer\Package\LinkConstraint\VersionConstraint;
use Composer\Package\PackageInterface;
use Composer\Repository\Vcs\VcsDriverInterface;
use Composer\Repository\VcsRepository;
use Composer\Util\RemoteFilesystem;
use DateTime;

class PackageManager
{
    public $config;

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
     * @param Entity\Package $package
     *
     * @return Entity\Package
     */
    public function syncPackage(Entity\Package $package)
    {
        $repository = $this->loadRepository($package);
        $information = $this->loadInformation($package);

        $versions = $repository->getPackages();
        $pv = [];
        foreach ($versions as $version) {
            $pv[] = $version->getPrettyVersion();
        }

        $package->setName($information['name']);
        if (isset($information['type'])) {
            $package->setType($information['type']);
        }
        if (isset($information['keywords'])) {
            $package->setKeywords($information['keywords']);
        }
        if (isset($information['authors'])) {
            $authors = [];
            foreach ($information['authors'] as $author) {
                $authors[] = $author['name'];
            }
            $package->setAuthors($authors);
        }
        if (isset($information['support'])) {
            $package->setSupport($information['support']);
        }

        if (isset($information['suggest'])) {
            $package->setSuggested($information['suggest']);
        }

        if (isset($information['extra']) && isset($information['extra']['bolt-screenshots'])) {
            $package->setScreenshots($information['extra']['bolt-screenshots']);
        }

        if (isset($information['extra']) && isset($information['extra']['bolt-icon'])) {
            $package->setIcon($information['extra']['bolt-icon']);
        }

        $package->setRequirements(json_encode($information['require']));
        $package->setVersions($pv);
        $package->setUpdated(new DateTime());

        return $package;
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
        $io = new NullIO();
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
        $io = new NullIO();
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
        $io = new NullIO();
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
        $v = new VersionConstraint('=', $boltVersion . '.0');

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
}
