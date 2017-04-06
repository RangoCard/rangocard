<?php

namespace Application\Migrations;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
class Version20170325202838 extends AbstractMigration
{
    /**
     * @param Schema $schema
     */
    public function up(Schema $schema)
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('ALTER TABLE user_points DROP INDEX UNIQ_42E89514A76ED395, ADD INDEX IDX_42E89514A76ED395 (user_id)');
        $this->addSql('ALTER TABLE user_points ADD sale_id INT DEFAULT NULL');
        $this->addSql('ALTER TABLE user_points ADD CONSTRAINT FK_42E895144A7E4868 FOREIGN KEY (sale_id) REFERENCES sale (id)');
        $this->addSql('CREATE INDEX IDX_42E895144A7E4868 ON user_points (sale_id)');
    }

    /**
     * @param Schema $schema
     */
    public function down(Schema $schema)
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('ALTER TABLE user_points DROP INDEX IDX_42E89514A76ED395, ADD UNIQUE INDEX UNIQ_42E89514A76ED395 (user_id)');
        $this->addSql('ALTER TABLE user_points DROP FOREIGN KEY FK_42E895144A7E4868');
        $this->addSql('DROP INDEX IDX_42E895144A7E4868 ON user_points');
        $this->addSql('ALTER TABLE user_points DROP sale_id');
    }
}
