<?php

namespace Bolt\Extension\Bolt\MarketPlace\Command;

use Bolt\Extension\Bolt\MarketPlace\Service\Queue\QueueManager;
use Bolt\Extension\Bolt\MarketPlace\Service\SatisManager;
use Bolt\Nut\BaseCommand;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
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
            ->setDescription('Processes the package update queue')
            ->setDefinition([
                new InputOption('cron', null, InputOption::VALUE_NONE, 'Running from cron.', null),
            ])
        ;
    }

    /**
     * {@inheritdoc}
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        /** @var SatisManager $satisManager */
        $satisManager = $this->app['marketplace.manager_satis'];

        /** @var QueueManager $queueManager */
        $queueManager = $this->app['marketplace.manager_queue'];

        $queueManager->processQueues($satisManager, $output);

        if (!$input->getOption('cron')) {
            $output->writeln('<info>Queue processed</info>');
        }
    }
}
