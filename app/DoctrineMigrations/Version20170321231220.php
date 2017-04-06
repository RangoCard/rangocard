<?php

namespace Application\Migrations;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
class Version20170321231220 extends AbstractMigration
{
    /**
     * @param Schema $schema
     */
    public function up(Schema $schema)
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('ALTER TABLE token CHANGE createdat created_at DATETIME NOT NULL');
        $this->addSql('ALTER TABLE user CHANGE createdat created_at DATETIME NOT NULL');
        $this->addSql('ALTER TABLE restaurant CHANGE createdat created_at DATETIME NOT NULL');
    }

    /**
     * @param Schema $schema
     */
    public function down(Schema $schema)
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('ALTER TABLE restaurant CHANGE created_at createdAt DATETIME NOT NULL');
        $this->addSql('ALTER TABLE token CHANGE created_at createdAt DATETIME NOT NULL');
        $this->addSql('ALTER TABLE user CHANGE created_at createdAt DATETIME NOT NULL');
    }
}
