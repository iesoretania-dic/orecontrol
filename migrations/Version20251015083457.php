<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20251015083457 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE active_rule (id INT AUTO_INCREMENT NOT NULL, network_id INT NOT NULL, rule_group_id INT NOT NULL, created_by_id INT DEFAULT NULL, created_at DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\', expires_at DATETIME DEFAULT NULL COMMENT \'(DC2Type:datetime_immutable)\', INDEX IDX_A8B83E1C34128B91 (network_id), INDEX IDX_A8B83E1C32A83AEB (rule_group_id), INDEX IDX_A8B83E1CB03A8386 (created_by_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE network (id INT AUTO_INCREMENT NOT NULL, name VARCHAR(255) NOT NULL, rule_description VARCHAR(255) NOT NULL, allowed_ip VARCHAR(255) NOT NULL, level INT NOT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE person (id INT AUTO_INCREMENT NOT NULL, username VARCHAR(255) NOT NULL, level INT NOT NULL, manager TINYINT(1) NOT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE rule_group (id INT AUTO_INCREMENT NOT NULL, name VARCHAR(255) NOT NULL, site_id VARCHAR(255) NOT NULL, _id VARCHAR(255) NOT NULL, group_type VARCHAR(255) NOT NULL, selectable TINYINT(1) NOT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE rule_log (id INT AUTO_INCREMENT NOT NULL, created_by_id INT DEFAULT NULL, deleted_by_id INT DEFAULT NULL, created_at DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\', expires_at DATETIME DEFAULT NULL COMMENT \'(DC2Type:datetime_immutable)\', deleted_at DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\', INDEX IDX_F8F32F37B03A8386 (created_by_id), INDEX IDX_F8F32F37C76F1F52 (deleted_by_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('ALTER TABLE active_rule ADD CONSTRAINT FK_A8B83E1C34128B91 FOREIGN KEY (network_id) REFERENCES network (id)');
        $this->addSql('ALTER TABLE active_rule ADD CONSTRAINT FK_A8B83E1C32A83AEB FOREIGN KEY (rule_group_id) REFERENCES rule_group (id)');
        $this->addSql('ALTER TABLE active_rule ADD CONSTRAINT FK_A8B83E1CB03A8386 FOREIGN KEY (created_by_id) REFERENCES person (id)');
        $this->addSql('ALTER TABLE rule_log ADD CONSTRAINT FK_F8F32F37B03A8386 FOREIGN KEY (created_by_id) REFERENCES person (id)');
        $this->addSql('ALTER TABLE rule_log ADD CONSTRAINT FK_F8F32F37C76F1F52 FOREIGN KEY (deleted_by_id) REFERENCES person (id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE active_rule DROP FOREIGN KEY FK_A8B83E1C34128B91');
        $this->addSql('ALTER TABLE active_rule DROP FOREIGN KEY FK_A8B83E1C32A83AEB');
        $this->addSql('ALTER TABLE active_rule DROP FOREIGN KEY FK_A8B83E1CB03A8386');
        $this->addSql('ALTER TABLE rule_log DROP FOREIGN KEY FK_F8F32F37B03A8386');
        $this->addSql('ALTER TABLE rule_log DROP FOREIGN KEY FK_F8F32F37C76F1F52');
        $this->addSql('DROP TABLE active_rule');
        $this->addSql('DROP TABLE network');
        $this->addSql('DROP TABLE person');
        $this->addSql('DROP TABLE rule_group');
        $this->addSql('DROP TABLE rule_log');
    }
}
