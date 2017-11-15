<?php

namespace Bundle\Site\MarketPlace\Command;

use Bundle\Site\MarketPlace\Service\PackageManager;
use Bundle\Site\MarketPlace\Storage\Entity;
use Bundle\Site\MarketPlace\Storage\Repository;
use Bolt\Extension\BoltAuth\Auth\Storage\Entity\Account as AuthAccountEntity;
use Bolt\Extension\BoltAuth\Auth\Storage\Repository\Account as AuthAccountRepository;
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
        $packageManager = $this->app['marketplace.manager_package'];
        /** @var Repository\Package $packageRepo */
        $packageRepo = $this->app['storage']->getRepository(Entity\Package::class);
        /** @var AuthAccountRepository $accountRepo */
        $accountRepo = $this->app['storage']->getRepository(AuthAccountEntity::class);

        if ($input->getOption('name')) {
            $package = $packageRepo->findOneBy(['name' => $input->getOption('name')]);
            if ($package === false) {
                $output->writeln('<error>Package not found!</error>');
                exit(255);
            }

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
            $packageManager->syncPackage($package);
            /** @var AuthAccountEntity $account */
            $account = $this->app['auth.records']->getAccountByGuid($package->getAccountId());
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
