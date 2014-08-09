<?php

namespace Bolt\Extensions;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
class Version20140809083252 extends AbstractMigration
{
    public function up(Schema $schema)
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() != "postgresql", "Migration can only be executed safely on 'postgresql'.");
        
        $this->addSql("CREATE TABLE Stat (id UUID NOT NULL, package_id UUID DEFAULT NULL, type VARCHAR(255) DEFAULT NULL, source VARCHAR(255) DEFAULT NULL, recorded TIMESTAMP(0) WITHOUT TIME ZONE DEFAULT NULL, PRIMARY KEY(id))");
        $this->addSql("CREATE INDEX IDX_808A501FF44CABFF ON Stat (package_id)");
        $this->addSql("ALTER TABLE Stat ADD CONSTRAINT FK_808A501FF44CABFF FOREIGN KEY (package_id) REFERENCES Package (id) NOT DEFERRABLE INITIALLY IMMEDIATE");
        $this->addSql("ALTER TABLE package ADD token VARCHAR(255) DEFAULT NULL");
        $this->addSql("ALTER TABLE package DROP installs");
        $this->addSql("ALTER TABLE package DROP stars");
    }

    public function down(Schema $schema)
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() != "postgresql", "Migration can only be executed safely on 'postgresql'.");
        
        $this->addSql("DROP TABLE Stat");
        $this->addSql("ALTER TABLE Package ADD installs INT DEFAULT NULL");
        $this->addSql("ALTER TABLE Package ADD stars INT DEFAULT NULL");
        $this->addSql("ALTER TABLE Package DROP token");
    }
}
