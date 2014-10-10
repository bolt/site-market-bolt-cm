<?php

namespace Bolt\Extensions;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
class Version20141010143137 extends AbstractMigration
{
    public function up(Schema $schema)
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() != 'mysql', 'Migration can only be executed safely on \'mysql\'.');
        
        $this->addSql('CREATE TABLE VersionBuild (id CHAR(36) NOT NULL COMMENT \'(DC2Type:guid)\', package_id CHAR(36) DEFAULT NULL COMMENT \'(DC2Type:guid)\', version VARCHAR(255) DEFAULT NULL, status VARCHAR(255) DEFAULT NULL, lastrun DATETIME DEFAULT NULL, url VARCHAR(255) DEFAULT NULL, hash VARCHAR(255) DEFAULT NULL, INDEX IDX_88656A38F44CABFF (package_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB');
        $this->addSql('ALTER TABLE VersionBuild ADD CONSTRAINT FK_88656A38F44CABFF FOREIGN KEY (package_id) REFERENCES Package (id)');
        $this->addSql('ALTER TABLE Stat ADD version VARCHAR(255) DEFAULT NULL');
    }

    public function down(Schema $schema)
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() != 'mysql', 'Migration can only be executed safely on \'mysql\'.');
        
        $this->addSql('DROP TABLE VersionBuild');
        $this->addSql('ALTER TABLE Stat DROP version');
    }
}
