<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20220607175359 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE destination_comment (id INT AUTO_INCREMENT NOT NULL, destination_id INT NOT NULL, user_id INT NOT NULL, body LONGTEXT NOT NULL, created_at DATETIME NOT NULL, INDEX IDX_FA4D6D14816C6140 (destination_id), INDEX IDX_FA4D6D14A76ED395 (user_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('ALTER TABLE destination_comment ADD CONSTRAINT FK_FA4D6D14816C6140 FOREIGN KEY (destination_id) REFERENCES destination (id)');
        $this->addSql('ALTER TABLE destination_comment ADD CONSTRAINT FK_FA4D6D14A76ED395 FOREIGN KEY (user_id) REFERENCES user (id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('DROP TABLE destination_comment');
    }
}
