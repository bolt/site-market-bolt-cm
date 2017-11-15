<?php

namespace Bundle\Site\MarketPlace\Service\Queue;

use Bundle\Site\MarketPlace\Location;
use Bundle\Site\MarketPlace\Service\SatisManager;
use Bundle\Site\MarketPlace\Storage\Entity;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\Filesystem\LockHandler;
use Symfony\Component\Finder\Finder;
use Symfony\Component\Finder\SplFileInfo;

/**
 * Package processing queue.
 *
 * @author Gawain Lynch <gawain.lynch@gmail.com>
 */
class PackageQueue extends AbstractQueue
{
    /**
     * @param Entity\Package $package
     */
    public function queue(Entity\Package $package)
    {
        $lockDir = $this->getCachePath(Location::SATIS_LOCK);
        $queueDir = $this->getCachePath(Location::SATIS_QUEUE_PACKAGE);

        $fs = new Filesystem();
        if (!$fs->exists($queueDir)) {
            $fs->mkdir($queueDir);
        }
        $packageLockFile = $queueDir . '/' . $package->getId();
        $fs->touch($packageLockFile);

        $lock = new LockHandler($package->getId(), $lockDir);
        if ($lock->lock()) {
            $fs->dumpFile($packageLockFile, $package->getName());
        }
        $lock->release();
    }

    /**
     * @param SatisManager    $manager
     * @param OutputInterface $output
     */
    public function process(SatisManager $manager, OutputInterface $output)
    {
        $lockDir = $this->getCachePath(Location::SATIS_LOCK);
        $queueDir = $this->getCachePath(Location::SATIS_QUEUE_PACKAGE);

        $fs = new Filesystem();
        if (!$fs->exists($queueDir)) {
            $fs->mkdir($queueDir);
        }

        $finder = new Finder();
        $files = $finder
            ->files()
            ->ignoreDotFiles(true)
            ->in($queueDir)
            ->depth(0)
        ;

        $manager->dumpSatisJson();

        /** @var SplFileInfo $file */
        foreach ($files as $file) {
            $this->processFile($file, $lockDir, $manager, $output);
        }
    }

    /**
     * @param SplFileInfo     $file
     * @param string          $lockDir
     * @param SatisManager    $manager
     * @param OutputInterface $output
     */
    protected function processFile(SplFileInfo $file, $lockDir, SatisManager $manager, OutputInterface $output)
    {
        $fs = new Filesystem();
        $lock = new LockHandler($file->getFilename(), $lockDir);

        if (!$lock->lock()) {
            $output->writeln(sprintf('<error>[Q] Unable to get lock on %s</error>', $file->getFilename()));

            return;
        }

        $packageName = $file->getContents();
        $output->writeln(sprintf('<info>[Q] Processing build for %s</info>', $packageName));
        $manager->build($packageName);
        $fs->remove($file->getRealPath());

        $lock->release();
    }
}
