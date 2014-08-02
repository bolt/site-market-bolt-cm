<?php

namespace Bolt\Extensions;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
class Version20140802125000 extends AbstractMigration
{
    public function up(Schema $schema)
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() != "postgresql", "Migration can only be executed safely on 'postgresql'.");
        
        $this->addSql("ALTER TABLE package ADD account_id UUID DEFAULT NULL");
        $this->addSql("ALTER TABLE package ADD CONSTRAINT FK_11D55E099B6B5FBA FOREIGN KEY (account_id) REFERENCES Account (id) NOT DEFERRABLE INITIALLY IMMEDIATE");
        $this->addSql("CREATE INDEX IDX_11D55E099B6B5FBA ON package (account_id)");
    }

    public function down(Schema $schema)
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() != "postgresql", "Migration can only be executed safely on 'postgresql'.");
        
        $this->addSql("ALTER TABLE Package DROP CONSTRAINT FK_11D55E099B6B5FBA");
        $this->addSql("DROP INDEX IDX_11D55E099B6B5FBA");
        $this->addSql("ALTER TABLE Package DROP account_id");
    }
}
