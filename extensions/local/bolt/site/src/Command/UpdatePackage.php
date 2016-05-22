<?php

namespace Bolt\Extension\Bolt\MarketPlace\Command;

use Bolt\Extension\Bolt\MarketPlace\Storage\Entity;
use Bolt\Extension\Bolt\MarketPlace\Storage\Repository;
use Bolt\Extension\Bolt\MarketPlace\Service\PackageManager;
use Bolt\Extension\Bolt\Members\Storage\Entity\Account as MembersAccountEntity;
use Bolt\Extension\Bolt\Members\Storage\Repository\Account as MembersAccountRepository;
use Bolt\Nut\BaseCommand;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Update package command.
 *
 * @author Ross Riley <riley.ross@gmail.com>
 * @author Gawain Lynch <gawain.lynch@gmail.com>
 */
class UpdatePackage extends BaseCommand
{
    /**
     * {@inheritdoc}
     */
    protected function configure()
    {
        $this->setName('package:update')
            ->setDescription('Updates registered extension(s)')
            ->addOption('all',    null, InputOption::VALUE_NONE,     'Update all packages')
            ->addOption('random', null, InputOption::VALUE_NONE,     'Update a random package')
            ->addOption('name',   null, InputOption::VALUE_REQUIRED, 'Specific package to update')
        ;
    }

    /**
     * {@inheritdoc}
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        /** @var PackageManager $packageManager */
        $packageManager = $this->app['marketplace.services']['package_manager'];
        /** @var Repository\Package $packageRepo */
        $packageRepo = $this->app['storage']->getRepository(Entity\Package::class);
        /** @var MembersAccountRepository $accountRepo */
        $accountRepo = $this->app['storage']->getRepository(MembersAccountEntity::class);

        if ($input->getOption('name')) {
            $package = $packageRepo->findOneBy(['name' => $input->getOption('name')]);
            /** @var Entity\Package $package */
            $this->updatePackage($packageRepo, $package, $packageManager, $output);
        } elseif ($input->getOption('random')) {
            $packages = $packageRepo->findBy(['approved' => true]);
            /** @var Entity\Package $package */
            $package = $packages[array_rand($packages)];
            $this->updatePackage($packageRepo, $package, $packageManager, $output);
        } elseif ($input->getOption('all')) {
            $packages = $packageRepo->findBy(['approved' => true]);
            /** @var Entity\Package $package */
            foreach ($packages as $package) {
                $this->updatePackage($packageRepo, $package, $packageManager, $output);
            }
        }

        $output->writeln('<comment>Update Complete</comment>');
    }

    /**
     * @param Repository\Package $repo
     * @param Entity\Package     $package
     * @param PackageManager     $packageManager
     * @param OutputInterface    $output
     */
    protected function updatePackage(Repository\Package $repo, Entity\Package $package, PackageManager $packageManager, OutputInterface $output)
    {
        $output->writeln('<info>Updating ' . $package->getName() . '</info>');
        try {
            $package = $packageManager->syncPackage($package);
            /** @var MembersAccountEntity $account */
            $account = $this->app['members.records']->getAccountByGuid($package->getAccountId());
            if ($account->isEnabled()) {
                $package->setApproved(true);
            }
        } catch (\Exception $e) {
            $output->writeln('<error>' . $e->getMessage() . '</error>');
            $package->setApproved(false);
        }

        $repo->save($package);
    }
}
