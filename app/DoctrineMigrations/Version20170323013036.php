<?php

namespace Application\Migrations;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
class Version20170323013036 extends AbstractMigration
{
    /**
     * @param Schema $schema
     */
    public function up(Schema $schema)
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('INSERT INTO `notification_type` (`id`, `name`) VALUES (1, \'NEW_SEAL\')');
        $this->addSql('INSERT INTO `notification_type` (`id`, `name`) VALUES (2, \'NEW_RESTAURANT\')');
        $this->addSql('INSERT INTO `notification_type` (`id`, `name`) VALUES (3, \'NEW_SALE\')');
    }

    /**
     * @param Schema $schema
     */
    public function down(Schema $schema)
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('DELETE FROM `notification_type` WHERE `id` = 1');
        $this->addSql('DELETE FROM `notification_type` WHERE `id` = 2');
        $this->addSql('DELETE FROM `notification_type` WHERE `id` = 3');
    }
}
