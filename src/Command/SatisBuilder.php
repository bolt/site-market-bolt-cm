<?php
namespace Bolt\Extension\Bolt\MarketPlace\Command;

use Bolt\Extension\Bolt\MarketPlace\Service\PackageManager;
use Bolt\Extension\Bolt\MarketPlace\Service\SatisManager;
use Bolt\Nut\BaseCommand;
use Composer\IO\BufferIO;
use Composer\Json\JsonValidationException;
use Seld\JsonLint\ParsingException;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Output\StreamOutput;
use Symfony\Component\Filesystem\Exception\FileNotFoundException;

/**
 * Satis builder command.
 *
 * @author Gawain Lynch <gawain.lynch@gmail.com>
 */
class SatisBuilder extends BaseCommand
{
    /**
     * {@inheritdoc}
     */
    protected function configure()
    {
        $this->setName('package:build')
            ->setDescription('Trigger build of the Satis repository.')
            ->setDefinition([
                new InputArgument('package', InputArgument::IS_ARRAY | InputArgument::OPTIONAL, 'Package that should be built, if not provided all packages are.', null),
            ])
        ;
    }

    /**
     * {@inheritdoc}
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $skipErrors = true;
        $packageName = $input->getArgument('package');

        /** @var SatisManager $satisProvider */
        $satisProvider = $this->app['marketplace.manager_satis'];
        $satisProvider->setConsoleOutput($output);

        try {
            $packages = $satisProvider->build($packageName);

            $output->write('<info>Updating package entities …</info>');
            /** @var PackageManager $packageManager */
            $packageManager = $this->app['marketplace.manager_package'];
            $packageManager->setIo(new BufferIO('', StreamOutput::VERBOSITY_NORMAL, $output->getFormatter()));

            // Update version entities
            $packageManager->updateEntities($this->app['storage'], $packages);
        } catch (FileNotFoundException $e) {
            $output->writeln('<error>File not found: ' . $satisProvider->getSatisJsonFilePath() . '</error>');
            if (!$skipErrors) {
                throw $e;
            }
        } catch (JsonValidationException $e) {
            foreach ($e->getErrors() as $error) {
                $output->writeln(sprintf('<error>%s</error>', $error));
            }
            if (!$skipErrors) {
                throw $e;
            }
            $output->writeln(sprintf('<warning>%s: %s</warning>', get_class($e), $e->getMessage()));
        } catch (ParsingException $e) {
            if (!$skipErrors) {
                throw $e;
            }
            $output->writeln(sprintf('<warning>%s: %s</warning>', get_class($e), $e->getMessage()));
        } catch (\UnexpectedValueException $e) {
            if (!$skipErrors) {
                throw $e;
            }
            $output->writeln(sprintf('<warning>%s: %s</warning>', get_class($e), $e->getMessage()));
        } catch (\Exception $e) {
            $output->writeln('<error>Satis build failed!</error>');
            throw $e;
        }
    }
}
