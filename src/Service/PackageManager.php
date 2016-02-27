<?php

namespace Bolt\Extensions\Service;

use Composer\Config;
use Composer\IO\NullIO;
use Composer\Json\JsonFile;
use Composer\Repository\VcsRepository;
use Composer\Package\Dumper\ArrayDumper;
use Composer\Package\LinkConstraint\VersionConstraint;
use Composer\Util\RemoteFilesystem;
use DateTime;

class PackageManager
{

    public $config;

    public function __construct(Config $config)
    {
        $this->config = $config;
    }


    public function syncPackage($package)
    {
        $repository = $this->loadRepository($package);
        $information = $this->loadInformation($package);

        $versions = $repository->getPackages();
        $pv = [];
        foreach($versions as $version) {
            $pv[]=$version->getPrettyVersion();
        }

        $package->setName($information['name']);
        if(isset($information['type'])) {
            $package->setType($information['type']);
        }
        if(isset($information['keywords'])) {
            $package->setKeywords(implode(',',$information['keywords']));
        }
        if(isset($information['authors'])) {
            $authors = [];
            foreach($information['authors'] as $author) {
                $authors[]=$author['name'];
            }
            $package->setAuthors(implode(',',$authors));
        }
        if(isset($information['support'])) {
            $package->setSupport($information['support']);
        }

        if (isset($information['extra']) && isset($information['extra']['bolt-screenshots'])) {
            $package->setScreenshots(implode(',', $information['extra']['bolt-screenshots']) );
        }

        if (isset($information['extra']) && isset($information['extra']['bolt-icon'])) {
            $package->setIcon($information['extra']['bolt-icon'] );
        }

        $package->setRequirements(json_encode($information['require']));
        $package->setVersions(implode(',', $pv));
        $package->updated = new DateTime;
        return $package;
    }

    public function loadInformation($package, $identifier = null)
    {
        $repository = $this->loadRepository($package);
        $driver = $repository->getDriver();
        if ($driver === null) {
            return false;
        }

        if (null === $identifier) {
            $identifier = $driver->getRootIdentifier();
        }
        $information = $driver->getComposerInformation($identifier);
        return $information;
    }

    public function loadRepository($package)
    {
        $io = new NullIO();
        $io->loadConfiguration($this->config);
        $repository = new VcsRepository(['url' => $package->getRawSource()], $io, $this->config);
        return $repository;
    }

    public function getVersions($package)
    {
        $rep = $this->loadRepository($package);
        $versions = $rep->getPackages();
        foreach($versions as $version) {
            $info[] = $version;
        }
        return $info;
    }

    public function getInfo($package, $boltVersion)
    {
        $info = [];
        $repo = $this->loadRepository($package);
        $versions = $repo->getPackages();
        $dumper = new ArrayDumper();
        $releaseInfo = $this->getReleaseInfo($package);

        foreach($versions as $version) {
            if(!$boltVersion || $this->isCompatible($version, $boltVersion)) {
                $data = $dumper->dump($version);
                $data['stability'] = $version->getStability();

                if ($releaseInfo) {
                    foreach ($releaseInfo as $rel) {
                        if ($version->getPrettyVersion() == $rel['tag_name']) {
                            $data['release'] = $rel;
                        }
                    }

                }

                $info[]= $data;
            }
        }


        return $info;
    }

    /**
     * If we have a Github repo this gets some extra information about the version
     * @param  string $package
     * @param  array $version
     * @return array
     */
    public function getReleaseInfo($package)
    {
        $io = new NullIO();
        $io->loadConfiguration($this->config);
        $rfs = new RemoteFilesystem($io, $this->config);


        try {
            $baseApiUrl = 'https://api.github.com/repos/'.str_replace("https://github.com/", "", $package->getSource())."/releases";
            $repoData = JsonFile::parseJson($rfs->getContents('github.com', $baseApiUrl, false), $baseApiUrl);
        } catch (\Exception $e) {
            return;
        }

        return $repoData;

    }

    /**
     * If we have a Github repo this gets the readme content for the package
     * @param  string $package
     * @return array
     */
    public function getReadme($package)
    {
        $io = new NullIO();
        $io->loadConfiguration($this->config);
        $rfs = new RemoteFilesystem($io, $this->config);


        try {
            $baseApiUrl = 'https://api.github.com/repos/'.str_replace("https://github.com/", "", $package->getSource())."/readme";
            $readme = JsonFile::parseJson($rfs->getContents('github.com', $baseApiUrl, false), $baseApiUrl);
        } catch (\Exception $e) {
            return;
        }

        return $readme;

    }

    public function isCompatible($version, $boltVersion)
    {
        $require = $version->getRequires();
        if (!isset($require['bolt/bolt'])) {
            return false;
        }
        $constraint = $require['bolt/bolt']->getConstraint();
        $v = new VersionConstraint("=", $boltVersion.".0");
        return $constraint->matches($v);
    }

    public function validate($package, $isAdmin = false)
    {
        $valid = true;
        $errors = [];
        $manifest = $this->loadInformation($package);

        if ($manifest === false) {
            $valid = false;
            $errors[] = "The repository URL you provided could not be loaded - Check that it points to a publicly readable repository.";
        } else {

            if(!isset($manifest['name']) || !preg_match('#^[a-z0-9]+/[a-z0-9\-]+#', $manifest['name'])) {
                $valid = false;
                $errors[] = "'name' in composer.json must be set, must be lowercase and contain only alphanumerics";
            }

            if(!isset($manifest['type']) ||  !preg_match('#^bolt-(theme|extension)#', $manifest['type'])) {
                $valid = false;
                $errors[] = "'type' in composer.json must be set, and must be either 'bolt-extension' or 'bolt-theme'";
            }

            if(!isset($manifest['require'])) {
                $valid = false;
                $errors[] = "'require' in composer.json must be set, and must provide Bolt version compatibility";
            }

            if(isset($manifest['name']) && substr($manifest['name'], 0,5) === 'bolt/') {
                if(!$isAdmin) {
                    $valid = false;
                    $errors[] = "package name uses a 'bolt/' prefix which is reserved for official extensions only.";
                }
            }
        }

        if(false === $valid) {
            throw new \InvalidArgumentException(join("\n\n",$errors));
        }

    }



}
