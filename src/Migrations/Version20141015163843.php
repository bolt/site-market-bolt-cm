<?php

namespace Bolt\Extensions;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
class Version20141015163843 extends AbstractMigration
{
    public function up(Schema $schema)
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() != 'mysql', 'Migration can only be executed safely on \'mysql\'.');
        
        $this->addSql('ALTER TABLE Stat ADD account_id CHAR(36) DEFAULT NULL COMMENT \'(DC2Type:guid)\'');
        $this->addSql('ALTER TABLE Stat ADD CONSTRAINT FK_808A501F9B6B5FBA FOREIGN KEY (account_id) REFERENCES Account (id)');
        $this->addSql('CREATE INDEX IDX_808A501F9B6B5FBA ON Stat (account_id)');
    }

    public function down(Schema $schema)
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() != 'mysql', 'Migration can only be executed safely on \'mysql\'.');
        
        $this->addSql('ALTER TABLE Stat DROP FOREIGN KEY FK_808A501F9B6B5FBA');
        $this->addSql('DROP INDEX IDX_808A501F9B6B5FBA ON Stat');
        $this->addSql('ALTER TABLE Stat DROP account_id');
    }
}
