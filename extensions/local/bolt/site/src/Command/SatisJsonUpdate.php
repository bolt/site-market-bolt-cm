<?php

namespace Bolt\Extension\Bolt\MarketPlace\Command;

use Bolt\Extension\Bolt\MarketPlace\Service\SatisManager;
use Bolt\Extension\Bolt\MarketPlace\Storage\Entity;
use Bolt\Nut\BaseCommand;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Filesystem\Exception\IOException;

/**
 * Satis JSON update command.
 *
 * @author Gawain Lynch <gawain.lynch@gmail.com>
 */
class SatisJsonUpdate extends BaseCommand
{
    /**
     * {@inheritdoc}
     */
    protected function configure()
    {
        $this->setName('package:dump')
            ->setDescription('Dumps a satis.json file from all registered packages');
    }

    /**
     * {@inheritdoc}
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        /** @var SatisManager $satisProvider */
        $satisProvider = $this->app['marketplace.services']['satis_manager'];
        try {
            $satisProvider->dump();
        } catch (IOException $e) {
            $output->writeln(sprintf('<error>Could not write Satis configuration to %s check file or directory permissions.</error>', $satisProvider->getSatisJsonPath()));
        }

        $output->writeln(sprintf('<info>Satis configuration written to %s</info>', $satisProvider->getSatisJsonPath()));
    }
}
