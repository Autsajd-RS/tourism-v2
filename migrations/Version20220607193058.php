<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20220607193058 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE wish_list (id INT AUTO_INCREMENT NOT NULL, user_id INT NOT NULL, type VARCHAR(255) NOT NULL, INDEX IDX_5B8739BDA76ED395 (user_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE wish_list_destination (wish_list_id INT NOT NULL, destination_id INT NOT NULL, INDEX IDX_2DC36C14D69F3311 (wish_list_id), INDEX IDX_2DC36C14816C6140 (destination_id), PRIMARY KEY(wish_list_id, destination_id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('ALTER TABLE wish_list ADD CONSTRAINT FK_5B8739BDA76ED395 FOREIGN KEY (user_id) REFERENCES user (id)');
        $this->addSql('ALTER TABLE wish_list_destination ADD CONSTRAINT FK_2DC36C14D69F3311 FOREIGN KEY (wish_list_id) REFERENCES wish_list (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE wish_list_destination ADD CONSTRAINT FK_2DC36C14816C6140 FOREIGN KEY (destination_id) REFERENCES destination (id) ON DELETE CASCADE');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE wish_list_destination DROP FOREIGN KEY FK_2DC36C14D69F3311');
        $this->addSql('DROP TABLE wish_list');
        $this->addSql('DROP TABLE wish_list_destination');
    }
}
