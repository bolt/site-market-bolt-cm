<?php

namespace Bolt\Extension\Bolt\MarketPlace\Command;

use Bolt\Extension\Bolt\MarketPlace\Location;
use Bolt\Extension\Bolt\MarketPlace\Storage\Entity;
use Bolt\Nut\BaseCommand;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\Filesystem\LockHandler;
use Symfony\Component\Process\Process;

/**
 * Extension test runner.
 *
 * @author Ross Riley <riley.ross@gmail.com>
 * @author Gawain Lynch <gawain.lynch@gmail.com>
 */
class ExtensionTestRunner extends BaseCommand
{
    /** @var int */
    protected $waitTime;
    /** @var string */
    protected $protocol;
    /** @var string */
    protected $privateKey;

    protected function configure()
    {
        $idFile = getenv('HOME') . '/.ssh/id_rsa';
        $this->setName('package:extension-tester')
            ->setDescription('Looks in the queue and launches a test instance of a Bolt with extension / version loaded.')
            ->addOption('wait',   null, InputOption::VALUE_OPTIONAL, 'Amount of time to sleep in between connection attempts', 5)
            ->addOption('protocol',   null, InputOption::VALUE_OPTIONAL, 'Connection protocol, either "http" or "https"', 'http')
            ->addOption('private-key',   null, InputOption::VALUE_OPTIONAL, 'Private key file for SSH connections', getenv('HOME') . '/.ssh/id_rsa')
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $this->waitTime = (int) $input->getOption('wait');
        $this->protocol = $input->getOption('protocol');
        $this->privateKey = $input->getOption('private-key');

        if (!in_array($this->protocol, ['http', 'https'])) {
            throw new \BadMethodCallException(sprintf("Bad protocol specified: %s.\n\nMust me either 'http' or 'https'", $this->protocol));
        }

        $lockDir = $this->app['resources']->getPath(Location::SATIS_LOCK);
        $lock = new LockHandler('extension.test.runner', $lockDir);

        while (true) {
            if ($lock->lock() && $build = $this->checkQueue()) {
                $this->startJob($build, $output);
            }
            $output->writeln('Sleeping for ' . $this->waitTime . ' seconds');
            $lock->release();
            sleep($this->waitTime);
        }
    }

    /**
     * @return Entity\VersionBuild
     */
    protected function checkQueue()
    {
        $repo = $this->app['storage']->getRepository(Entity\VersionBuild::class);
        $build = $repo->findOneBy(['status' => 'waiting']);

        return $build;
    }

    /**
     * @param Entity\VersionBuild $build
     * @param OutputInterface     $output
     */
    protected function startJob(Entity\VersionBuild $build, OutputInterface $output)
    {
        $versionRepo = $this->app['storage']->getRepository(Entity\VersionBuild::class);
        $packageRepo = $this->app['storage']->getRepository(Entity\Package::class);
        /** @var Entity\Package $package */
        $package = $packageRepo->findOneBy(['id' => $build->getPackageId()]);

        $build->setStatus('building');
        $versionRepo->save($build);

        $command = sprintf(
            'ssh -i %s boltrunner@bolt.rossriley.co.uk "bundle exec cap production docker:run package=%s version=%s %s"',
            $this->getSshIdFilePath(),
            $package->getName(),
            $build->getVersion(),
            $build->getPhpTarget() ? 'php=' . $build->getPhpTarget() : ''
        );

        $process = new Process($command);
        $process->mustRun();

        if ($process->isSuccessful()) {
            $response = $process->getOutput();
            $lines = explode("\n", $response);
            if (!isset($lines[2])) {
                // This means the container couldn't launch a new instance.
                // Best bet here is to remain in waiting mode and try again next loop
                return;
            }
            $build->setStatus('complete');
            $build->setUrl(sprintf('%s%s', $this->protocol, $lines[2]));
            $build->setLastrun(new \DateTime());

            $output->writeln($build->getStatus());
            $output->writeln(sprintf(
                '<info>Built %s version %s to %s</info>',
                $package->getName(),
                $build->getVersion(),
                $build->getUrl()
            ));
        } else {
            $build->setStatus('failed');
        }

        $versionRepo->save($build);
    }

    /**
     * @throws \RuntimeException
     *
     * @return string
     */
    protected function getSshIdFilePath()
    {
        $fs = new Filesystem();
        if ($fs->exists($this->privateKey)) {
            return $this->privateKey;
        }

        throw new \RuntimeException(sprintf('SSH private key file not found at %s', $this->privateKey));
    }
}
