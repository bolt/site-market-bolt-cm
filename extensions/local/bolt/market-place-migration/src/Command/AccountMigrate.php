<?php

namespace Bolt\Extension\Bolt\MarketPlaceMigration\Command;

use Bolt\Extension\Bolt\MarketPlace\Storage\Entity as MarketPlaceEntity;
use Bolt\Extension\Bolt\MarketPlace\Storage\Repository as MarketPlaceRepository;
use Bolt\Extension\Bolt\MarketPlaceMigration\Storage\Entity;
use Bolt\Extension\Bolt\MarketPlaceMigration\Storage\Repository;
use Bolt\Extension\Bolt\Members\Storage\Entity\Account as MembersAccountEntity;
use Bolt\Extension\Bolt\Members\Storage\Entity\Oauth as MembersOauthEntity;
use Bolt\Extension\Bolt\Members\Storage\Records;
use Bolt\Extension\Bolt\Members\Storage\Repository\Account as MembersAccountRepository;
use Bolt\Extension\Bolt\Members\Storage\Repository\Oauth as MembersOauthRepository;
use Bolt\Nut\BaseCommand;
use Bolt\Storage\QuerySet;
use Bolt\Storage\Repository as BoltRepository;
use Doctrine\DBAL\Exception\UniqueConstraintViolationException;
use Ramsey\Uuid\Uuid;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class AccountMigrate extends BaseCommand
{
    /** @var array */
    private $success = [];
    /** @var array */
    private $errors = [];

    protected function configure()
    {
        $this->setName('account:migrate')
                ->setDescription('Migrate the account database.')
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $app = $this->app;

        /** @var MembersAccountRepository $membersAccountRepo */
        $membersAccountRepo = $app['storage']->getRepository(MembersAccountEntity::class);
        /** @var MembersOauthRepository $membersOauthRepo */
        $membersOauthRepo = $app['storage']->getRepository(MembersOauthEntity::class);

        /** @var Repository\Account $accountRepo */
        $accountRepo = $app['storage']->getRepository(Entity\Account::class);

        /** @var MarketPlaceRepository\Package $packageRepo */
        $packageRepo = $app['storage']->getRepository(MarketPlaceEntity\Package::class);
        /** @var MarketPlaceRepository\Stat $statRepo */
        $statRepo = $app['storage']->getRepository(MarketPlaceEntity\Stat::class);

        $accounts = $accountRepo->findAll();
        if ($accounts === false) {
            return;
        }

        /** @var Entity\Account $account */
        foreach ($accounts as $account) {
            $output->writeln(sprintf('<info>Migrating %s (%s)</info>', $account->getEmail(), $account->getId()));
            $this->migrateAccount($membersAccountRepo, $membersOauthRepo, $account, $output);
        }

        foreach ($this->errors as $error) {
            $this->mergeAccountPackages($packageRepo, $statRepo, $error);
        }
    }

    protected function migrateAccount(
        MembersAccountRepository $membersAccountRepo,
        MembersOauthRepository $membersOauthRepo,
        Entity\Account $account,
        OutputInterface $output
    ) {
        $app = $this->app;
        /** @var Records $membersRecords */
        $membersRecords = $app['members.records'];

        $guid = $account->getId();
        $email = strtolower($account->getEmail());
        $memberAccount = new MembersAccountEntity([
            'guid'        => $guid,
            'email'       => $email,
            'displayname' => $account->getName(),
            'enabled'     => $account->isApproved(),
            'verified'    => true,
            'roles'       => $account->isAdmin() ? ['admin'] : [],
            'lastseen'    => $account->getCreated(),
            'lastip'      => null,
        ]);

        $membersOauth = new MembersOauthEntity([
            'id'                => Uuid::uuid4()->toString(),
            'guid'              => $guid,
            'resource_owner_id' => $guid,
            'password'          => $account->getPassword(),
            'enabled'           => $account->isApproved(),
        ]);

        try {
            $this->insert($membersAccountRepo, $memberAccount);
            $this->insert($membersOauthRepo, $membersOauth);

            $membersRecords->createProvision($guid, 'local', $guid);

            $this->success[$email] = $memberAccount;
        } catch (UniqueConstraintViolationException $e) {
            $this->errors[] = [
                'guid'  => $guid,
                'email' => $email,
            ];

            $output->writeln(sprintf('<error>Duplicate email: %s</error>', $account->getEmail()));
        }
    }

    /**
     * @param MarketPlaceRepository\Package $packageRepo
     * @param MarketPlaceRepository\Stat    $statRepo
     * @param array                         $error
     */
    protected function mergeAccountPackages(
        MarketPlaceRepository\Package $packageRepo,
        MarketPlaceRepository\Stat $statRepo,
        array $error
    ) {
        $email = $error['email'];
        /** @var MembersAccountEntity $account */
        $account = $this->success[$email];
        $oldGuid = $error['guid'];
        $newGuid = $account->getGuid();

        $packages = $packageRepo->findBy(['account_id' => $oldGuid]);
        /** @var MarketPlaceEntity\Package $package */
        foreach ($packages as $package) {
            $package->setAccountId($newGuid);
            $packageRepo->save($package);
        }

        $stats = $statRepo->findBy(['account_id' => $oldGuid]);
        /** @var MarketPlaceEntity\Stat $stat */
        foreach ($stats as $stat) {
            $stat->setAccountId($newGuid);
            $statRepo->save($stat);
        }
    }

    /**
     * @param BoltRepository $repo
     * @param object         $entity
     *
     * @throws \Exception
     *
     * @return \Doctrine\DBAL\Driver\Statement|int|null
     */
    protected function insert(BoltRepository $repo, $entity)
    {
        $querySet = new QuerySet();
        $qb = $repo->em->createQueryBuilder();
        $qb->insert($repo->getTableName());
        $querySet->append($qb);
        $this->persist($repo, $querySet, $entity, ['id']);

        $result = $querySet->execute();

        return $result;
    }

    /**
     * @param BoltRepository $repo
     * @param QuerySet       $queries
     * @param object         $entity
     * @param array          $exclusions
     */
    protected function persist(BoltRepository $repo, QuerySet $queries, $entity, $exclusions = [])
    {
        $metadata = $repo->getClassMetadata();

        foreach ($metadata->getFieldMappings() as $field) {
            if (in_array($field['fieldname'], $exclusions)) {
                continue;
            }

            $field = $repo->getFieldManager()->get($field['fieldtype'], $field);
            $field->persist($queries, $entity);
        }
    }
}
