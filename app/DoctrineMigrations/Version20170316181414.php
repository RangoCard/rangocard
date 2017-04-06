<?php

namespace Application\Migrations;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
class Version20170316181414 extends AbstractMigration
{
    /**
     * @param Schema $schema
     */
    public function up(Schema $schema)
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('CREATE TABLE restaurant (id INT AUTO_INCREMENT NOT NULL, name VARCHAR(255) NOT NULL, email VARCHAR(255) NOT NULL, password VARCHAR(255) NOT NULL, cnpj VARCHAR(30) NOT NULL, fantasy_name VARCHAR(255) DEFAULT NULL, site VARCHAR(255) DEFAULT NULL, phone VARCHAR(30) DEFAULT NULL, whatsapp VARCHAR(30) DEFAULT NULL, cep VARCHAR(10) DEFAULT NULL, city VARCHAR(255) DEFAULT NULL, state VARCHAR(2) DEFAULT NULL, street VARCHAR(255) DEFAULT NULL, district VARCHAR(255) DEFAULT NULL, salt VARCHAR(255) NOT NULL, createdAt DATETIME NOT NULL, updated_at DATETIME DEFAULT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB');
        $this->addSql('ALTER TABLE user ADD cpf VARCHAR(30) NOT NULL, ADD phone VARCHAR(30) DEFAULT NULL, ADD cep VARCHAR(10) DEFAULT NULL, ADD city VARCHAR(255) DEFAULT NULL, ADD state VARCHAR(2) DEFAULT NULL, ADD street VARCHAR(255) DEFAULT NULL, ADD district VARCHAR(255) DEFAULT NULL');
    }

    /**
     * @param Schema $schema
     */
    public function down(Schema $schema)
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('DROP TABLE restaurant');
        $this->addSql('ALTER TABLE user DROP cpf, DROP phone, DROP cep, DROP city, DROP state, DROP street, DROP district');
    }
}
