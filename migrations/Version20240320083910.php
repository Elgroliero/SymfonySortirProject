<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20240320083910 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE `groups` ADD group_creator_id INT DEFAULT NULL');
        $this->addSql('ALTER TABLE `groups` ADD CONSTRAINT FK_F06D3970B6EF6F14 FOREIGN KEY (group_creator_id) REFERENCES participant (id)');
        $this->addSql('CREATE INDEX IDX_F06D3970B6EF6F14 ON `groups` (group_creator_id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE `groups` DROP FOREIGN KEY FK_F06D3970B6EF6F14');
        $this->addSql('DROP INDEX IDX_F06D3970B6EF6F14 ON `groups`');
        $this->addSql('ALTER TABLE `groups` DROP group_creator_id');
    }
}
