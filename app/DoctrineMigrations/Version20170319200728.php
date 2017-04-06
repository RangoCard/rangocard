<?php

namespace Application\Migrations;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
class Version20170319200728 extends AbstractMigration
{
    /**
     * @param Schema $schema
     */
    public function up(Schema $schema)
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('CREATE TABLE token (id INT AUTO_INCREMENT NOT NULL, restaurant_id INT DEFAULT NULL, token VARCHAR(30) NOT NULL, createdAt DATETIME NOT NULL, updated_at DATETIME DEFAULT NULL, INDEX IDX_5F37A13BB1E7706E (restaurant_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB');
        $this->addSql('CREATE TABLE sale (id INT AUTO_INCREMENT NOT NULL, restaurant_id INT DEFAULT NULL, name VARCHAR(255) NOT NULL, description VARCHAR(255) DEFAULT NULL, seal_limit INT NOT NULL, createdAt DATETIME NOT NULL, updated_at DATETIME DEFAULT NULL, INDEX IDX_E54BC005B1E7706E (restaurant_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB');
        $this->addSql('CREATE TABLE seal (id INT AUTO_INCREMENT NOT NULL, sale_id INT DEFAULT NULL, restaurant_id INT DEFAULT NULL, user_id INT DEFAULT NULL, token VARCHAR(30) NOT NULL, createdAt DATETIME NOT NULL, updated_at DATETIME DEFAULT NULL, INDEX IDX_2E30AE304A7E4868 (sale_id), INDEX IDX_2E30AE30B1E7706E (restaurant_id), INDEX IDX_2E30AE30A76ED395 (user_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB');
        $this->addSql('ALTER TABLE token ADD CONSTRAINT FK_5F37A13BB1E7706E FOREIGN KEY (restaurant_id) REFERENCES restaurant (id)');
        $this->addSql('ALTER TABLE sale ADD CONSTRAINT FK_E54BC005B1E7706E FOREIGN KEY (restaurant_id) REFERENCES restaurant (id)');
        $this->addSql('ALTER TABLE seal ADD CONSTRAINT FK_2E30AE304A7E4868 FOREIGN KEY (sale_id) REFERENCES sale (id)');
        $this->addSql('ALTER TABLE seal ADD CONSTRAINT FK_2E30AE30B1E7706E FOREIGN KEY (restaurant_id) REFERENCES restaurant (id)');
        $this->addSql('ALTER TABLE seal ADD CONSTRAINT FK_2E30AE30A76ED395 FOREIGN KEY (user_id) REFERENCES user (id)');
    }

    /**
     * @param Schema $schema
     */
    public function down(Schema $schema)
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('ALTER TABLE seal DROP FOREIGN KEY FK_2E30AE304A7E4868');
        $this->addSql('DROP TABLE token');
        $this->addSql('DROP TABLE sale');
        $this->addSql('DROP TABLE seal');
    }
}
