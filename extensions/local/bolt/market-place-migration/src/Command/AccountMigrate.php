<?php

namespace Bolt\Extension\Bolt\MarketPlaceMigration\Command;

use Bolt\Extension\Bolt\MarketPlaceMigration\Storage\Entity;
use Bolt\Extension\Bolt\MarketPlaceMigration\Storage\Repository;
use Bolt\Extension\Bolt\Members\Storage\Entity\Account as MembersAccountEntity;
use Bolt\Extension\Bolt\Members\Storage\Entity\Oauth as MembersOauthEntity;
use Bolt\Extension\Bolt\Members\Storage\Records;
use Bolt\Extension\Bolt\Members\Storage\Repository\Account as MembersAccountRepository;
use Bolt\Extension\Bolt\Members\Storage\Repository\Oauth as MembersOauthRepository;
use Bolt\Nut\BaseCommand;
use Bolt\Storage\QuerySet;
use Doctrine\DBAL\Exception\UniqueConstraintViolationException;
use Ramsey\Uuid\Uuid;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class AccountMigrate extends BaseCommand
{
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

        $accounts = $accountRepo->findAll();
        if ($accounts === false) {
            return;
        }

        /** @var Entity\Account $account */
        foreach ($accounts as $account) {
            $output->writeln(sprintf('<info>Migrating %s (%s)</info>', $account->getEmail(), $account->getId()));
            $this->migrateAccount($membersAccountRepo, $membersOauthRepo, $account, $output);
        }
    }

    protected function migrateAccount(
        MembersAccountRepository $membersAccountRepo,
        MembersOauthRepository $membersOauthRepo,
        Entity\Account $account,
        OutputInterface $output
    ) {
        $memberAccount = new MembersAccountEntity([
            'guid'        => $account->getId(),
            'email'       => $account->getEmail(),
            'displayname' => $account->getName(),
            'enabled'     => $account->isApproved(),
            'verified'    => true,
            'roles'       => $account->isAdmin() ? ['admin'] : [],
            'lastseen'    => $account->getCreated(),
            'lastip'      => null,
        ]);

        $membersOauth = new MembersOauthEntity([
            'id'                => Uuid::uuid4()->toString(),
            'guid'              => $account->getId(),
            'resource_owner_id' => $account->getId(),
            'password'          => $account->getPassword(),
            'enabled'           => $account->isApproved(),
        ]);

        try {
            $this->insert($membersAccountRepo, $memberAccount);
            $this->insert($membersOauthRepo, $membersOauth);

            $app = $this->app;
            $app['members.records']->createProvision($account->getId(), 'local', $account->getId());
        } catch (UniqueConstraintViolationException $e) {
            $output->writeln(sprintf('<error>    Error: %s</error>', $e->getMessage()));
        }
    }

    protected function insert(\Bolt\Storage\Repository $repo, $entity)
    {
        $querySet = new QuerySet();
        $qb = $repo->em->createQueryBuilder();
        $qb->insert($repo->getTableName());
        $querySet->append($qb);
        $this->persist($repo, $querySet, $entity, ['id']);

        $result = $querySet->execute();

        return $result;
    }

    protected function persist(\Bolt\Storage\Repository $repo, QuerySet $queries, $entity, $exclusions = [])
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
