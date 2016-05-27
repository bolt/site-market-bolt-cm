<?php

namespace Bolt\Extension\Bolt\MarketPlace\Command;

use Bolt\Extension\Bolt\MarketPlace\Service\SatisManager;
use Bolt\Nut\BaseCommand;
use Composer\IO\ConsoleIO;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Satis web index builder command.
 *
 * @author Gawain Lynch <gawain.lynch@gmail.com>
 */
class SatisBuilderWebIndex extends BaseCommand
{
    /**
     * {@inheritdoc}
     */
    protected function configure()
    {
        $this->setName('package:build-web')
            ->setDescription('Manually rebuild satis web index.')
        ;
    }

    /**
     * {@inheritdoc}
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        /** @var SatisManager $satisProvider */
        $satisProvider = $this->app['marketplace.services']['satis_manager'];
        $satisProvider->setIo(new ConsoleIO($input, $output, $this->getHelperSet()));

        $packages = $satisProvider->getBuiltPackages($output, true);

        $satisProvider->dumpPackages($packages, $output, true);
    }
}
