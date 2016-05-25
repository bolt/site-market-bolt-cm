<?php
namespace Bolt\Extension\Bolt\MarketPlace\Command;

use Bolt\Extension\Bolt\MarketPlace\Service\SatisManager;
use Bolt\Nut\BaseCommand;
use Composer\Json\JsonValidationException;
use Seld\JsonLint\ParsingException;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
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
            ->setDescription('Trigger build of satis repos.')
            ->setDefinition([
                new InputArgument('packages', InputArgument::IS_ARRAY | InputArgument::OPTIONAL, 'Packages that should be built, if not provided all packages are.', null)
            ])
        ;
    }

    /**
     * {@inheritdoc}
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $packagesFilter = (array) $input->getArgument('packages');

        /** @var SatisManager $satisProvider */
        $satisProvider = $this->app['marketplace.services']['satis_manager'];
        $skipErrors = true;

        try {
            $satisProvider->build($packagesFilter, $output);
            $output->writeln('<info>Satis file built…</info>');
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
