<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20240318152103 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE `groups` (id INT AUTO_INCREMENT NOT NULL, nom VARCHAR(255) NOT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE groups_participant (groups_id INT NOT NULL, participant_id INT NOT NULL, INDEX IDX_4CA061D8F373DCF (groups_id), INDEX IDX_4CA061D89D1C3019 (participant_id), PRIMARY KEY(groups_id, participant_id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('ALTER TABLE groups_participant ADD CONSTRAINT FK_4CA061D8F373DCF FOREIGN KEY (groups_id) REFERENCES `groups` (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE groups_participant ADD CONSTRAINT FK_4CA061D89D1C3019 FOREIGN KEY (participant_id) REFERENCES participant (id) ON DELETE CASCADE');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE groups_participant DROP FOREIGN KEY FK_4CA061D8F373DCF');
        $this->addSql('ALTER TABLE groups_participant DROP FOREIGN KEY FK_4CA061D89D1C3019');
        $this->addSql('DROP TABLE `groups`');
        $this->addSql('DROP TABLE groups_participant');
    }
}
