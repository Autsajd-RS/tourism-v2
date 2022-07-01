<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20220605133558 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE category (id INT AUTO_INCREMENT NOT NULL, name VARCHAR(255) NOT NULL, icon VARCHAR(255) NOT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE destination (id INT AUTO_INCREMENT NOT NULL, city_id INT NOT NULL, category_id INT NOT NULL, name VARCHAR(255) NOT NULL, address VARCHAR(255) NOT NULL, longitude VARCHAR(255) NOT NULL, latitude VARCHAR(255) NOT NULL, description LONGTEXT DEFAULT NULL, popularity INT DEFAULT NULL, attendance INT DEFAULT NULL, INDEX IDX_3EC63EAA8BAC62AF (city_id), INDEX IDX_3EC63EAA12469DE2 (category_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('ALTER TABLE destination ADD CONSTRAINT FK_3EC63EAA8BAC62AF FOREIGN KEY (city_id) REFERENCES city (id)');
        $this->addSql('ALTER TABLE destination ADD CONSTRAINT FK_3EC63EAA12469DE2 FOREIGN KEY (category_id) REFERENCES category (id)');
        $this->addSql('ALTER TABLE user ADD CONSTRAINT FK_8D93D6498BAC62AF FOREIGN KEY (city_id) REFERENCES city (id)');
        $this->addSql('CREATE INDEX IDX_8D93D6498BAC62AF ON user (city_id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE destination DROP FOREIGN KEY FK_3EC63EAA12469DE2');
        $this->addSql('DROP TABLE category');
        $this->addSql('DROP TABLE destination');
        $this->addSql('ALTER TABLE user DROP FOREIGN KEY FK_8D93D6498BAC62AF');
        $this->addSql('DROP INDEX IDX_8D93D6498BAC62AF ON user');
    }
}
