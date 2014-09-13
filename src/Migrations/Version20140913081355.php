<?php

namespace Bolt\Extensions;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
class Version20140913081355 extends AbstractMigration
{
    public function up(Schema $schema)
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() != "mysql", "Migration can only be executed safely on 'mysql'.");
        
        $this->addSql("CREATE TABLE Account (id VARCHAR(255) NOT NULL COMMENT '(DC2Type:guid)', email VARCHAR(255) DEFAULT NULL, username VARCHAR(255) DEFAULT NULL, password VARCHAR(255) DEFAULT NULL, name VARCHAR(255) DEFAULT NULL, admin TINYINT(1) DEFAULT '0', approved TINYINT(1) DEFAULT '1', created DATETIME DEFAULT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB");
        $this->addSql("CREATE TABLE Package (id VARCHAR(255) NOT NULL COMMENT '(DC2Type:guid)', account_id VARCHAR(255) DEFAULT NULL COMMENT '(DC2Type:guid)', source VARCHAR(255) DEFAULT NULL, title VARCHAR(255) DEFAULT NULL, name VARCHAR(255) DEFAULT NULL, keywords VARCHAR(255) DEFAULT NULL, type VARCHAR(255) DEFAULT NULL, description LONGTEXT DEFAULT NULL, documentation LONGTEXT DEFAULT NULL, approved TINYINT(1) DEFAULT '1', versions VARCHAR(255) DEFAULT NULL, requirements VARCHAR(255) DEFAULT NULL, authors VARCHAR(255) DEFAULT NULL, created DATETIME DEFAULT NULL, updated DATETIME DEFAULT NULL, token VARCHAR(255) DEFAULT NULL, INDEX IDX_11D55E099B6B5FBA (account_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB");
        $this->addSql("CREATE TABLE Stat (id VARCHAR(255) NOT NULL COMMENT '(DC2Type:guid)', package_id VARCHAR(255) DEFAULT NULL COMMENT '(DC2Type:guid)', type VARCHAR(255) DEFAULT NULL, source VARCHAR(255) DEFAULT NULL, ip VARCHAR(255) DEFAULT NULL, recorded DATETIME DEFAULT NULL, INDEX IDX_808A501FF44CABFF (package_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB");
        $this->addSql("ALTER TABLE Package ADD CONSTRAINT FK_11D55E099B6B5FBA FOREIGN KEY (account_id) REFERENCES Account (id)");
        $this->addSql("ALTER TABLE Stat ADD CONSTRAINT FK_808A501FF44CABFF FOREIGN KEY (package_id) REFERENCES Package (id)");
    }

    public function down(Schema $schema)
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() != "mysql", "Migration can only be executed safely on 'mysql'.");
        
        $this->addSql("ALTER TABLE Package DROP FOREIGN KEY FK_11D55E099B6B5FBA");
        $this->addSql("ALTER TABLE Stat DROP FOREIGN KEY FK_808A501FF44CABFF");
        $this->addSql("DROP TABLE Account");
        $this->addSql("DROP TABLE Package");
        $this->addSql("DROP TABLE Stat");
    }
}
