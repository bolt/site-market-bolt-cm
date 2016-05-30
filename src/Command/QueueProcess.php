<?php

namespace Bolt\Extension\Bolt\MarketPlace\Command;

use Bolt\Extension\Bolt\MarketPlace\Service\PackageManager;
use Bolt\Extension\Bolt\MarketPlace\Service\SatisManager;
use Bolt\Nut\BaseCommand;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Queued packages update runner.
 *
 * @author Gawain Lynch <gawain.lynch@gmail.com>
 */
class QueueProcess extends BaseCommand
{
    /**
     * {@inheritdoc}
     */
    protected function configure()
    {
        $this->setName('package:queue-process')
            ->setDescription('Processes the package update queue');
    }

    /**
     * {@inheritdoc}
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        /** @var PackageManager $packageProvider */
        $packageProvider = $this->app['marketplace.services']['package_manager'];
        /** @var SatisManager $satisProvider */
        $satisProvider = $this->app['marketplace.services']['satis_manager'];
        $satisProvider->processQueue($packageProvider, $output);
        
        $output->writeln('<info>Queue processed</info>');
    }
}
