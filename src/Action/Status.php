<?php

namespace Bundle\Site\MarketPlace\Action;

use Bundle\Site\MarketPlace\Service\SatisManager;
use Bolt\Filesystem\Filesystem;
use Bolt\Filesystem\Handler\FileInterface;
use Composer\Package\CompletePackageInterface;
use Composer\Package\PackageInterface;
use Symfony\Component\Console\Output\BufferedOutput;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Status page action.
 *
 * @author Gawain Lynch <gawain.lynch@gmail.com>
 */
class Status extends AbstractAction
{
    /**
     * {@inheritdoc}
     */
    public function execute(Request $request, array $params)
    {
        $output = new BufferedOutput();

        /** @var Filesystem $resources */
        $resources = $this->getAppService('filesystem');
        /** @var FileInterface $indexFile */
        $indexFile = $resources->get('web/satis/index.html');

        /** @var SatisManager $packageManager */
        $satisManager = $this->getAppService('marketplace.manager_satis');
        $packages = $satisManager->getBuiltPackages($output, true);

        /** @var \Twig_Environment $twig */
        $twig = $this->getAppService('twig');

        $context = [
            'name'         => 'Bolt Market Place Repository',
            'url'          => null,
            'description'  => null,
            'packages'     => $this->getMappedPackageList($packages),
            'dependencies' => $this->setDependencies($packages),
            'lastupdate'   => $indexFile->exists() ? $indexFile->getTimestamp() : 0,
        ];
        $html = $twig->render('status.twig', $context);

        return new Response($html);
    }

    /**
     * Defines the required packages.
     *
     * @param PackageInterface[] $packages List of packages to dump
     *
     * @return string[]
     */
    private function setDependencies(array $packages)
    {
        $dependencies = [];
        foreach ($packages as $package) {
            foreach ($package->getRequires() as $link) {
                $dependencies[$link->getTarget()][$link->getSource()] = $link->getSource();
            }
        }

        return $dependencies;
    }

    /**
     * Gets a list of packages grouped by name with a list of versions.
     *
     * @param PackageInterface[] $packages List of packages to dump
     *
     * @return array Grouped list of packages with versions
     */
    private function getMappedPackageList(array $packages)
    {
        $groupedPackages = $this->groupPackagesByName($packages);

        $mappedPackages = [];
        foreach ($groupedPackages as $name => $packages) {
            $highest = $this->getHighestVersion($packages);

            $mappedPackages[$name] = [
                'highest'     => $highest,
                'abandoned'   => $highest instanceof CompletePackageInterface ? $highest->isAbandoned() : false,
                'replacement' => $highest instanceof CompletePackageInterface ? $highest->getReplacementPackage() : null,
                'versions'    => $this->getDescSortedVersions($packages),
            ];
        }

        return $mappedPackages;
    }

    /**
     * Gets a list of packages grouped by name.
     *
     * @param PackageInterface[] $packages List of packages to dump
     *
     * @return array List of packages grouped by name
     */
    private function groupPackagesByName(array $packages)
    {
        $groupedPackages = [];
        foreach ($packages as $package) {
            $groupedPackages[$package->getName()][] = $package;
        }

        return $groupedPackages;
    }

    /**
     * Gets the highest version of packages.
     *
     * @param PackageInterface[] $packages List of packages to dump
     *
     * @return PackageInterface The package with the highest version
     */
    private function getHighestVersion(array $packages)
    {
        /** @var $highestVersion PackageInterface|null */
        $highestVersion = null;
        foreach ($packages as $package) {
            if (null === $highestVersion || version_compare($package->getVersion(), $highestVersion->getVersion(), '>=')) {
                $highestVersion = $package;
            }
        }

        return $highestVersion;
    }

    /**
     * Sorts by version the list of packages.
     *
     * @param PackageInterface[] $packages List of packages to dump
     *
     * @return PackageInterface[] Sorted list of packages by version
     */
    private function getDescSortedVersions(array $packages)
    {
        usort($packages, function (PackageInterface $a, PackageInterface $b) {
            return version_compare($b->getVersion(), $a->getVersion());
        });

        return $packages;
    }
}
