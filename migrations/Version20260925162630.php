<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20260925162630 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql("ALTER TABLE person ADD password VARCHAR(255) DEFAULT NULL, ADD external TINYINT(1) NOT NULL DEFAULT 0, ADD active TINYINT(1) NOT NULL DEFAULT 1");
        $this->addSql('CREATE UNIQUE INDEX UNIQ_34DCD176F85E0677 ON person (username)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('DROP INDEX UNIQ_34DCD176F85E0677 ON person');
        $this->addSql('ALTER TABLE person DROP password, DROP external, DROP active');
    }
}
