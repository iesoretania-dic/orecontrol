<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20260925193723 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE rule_log ADD network_id INT NOT NULL, ADD rule_group_id INT DEFAULT NULL, DROP expires_at, CHANGE deleted_at deleted_at DATETIME DEFAULT NULL COMMENT \'(DC2Type:datetime_immutable)\', CHANGE created_ip created_ip VARCHAR(255) DEFAULT NULL, CHANGE deleted_ip deleted_ip VARCHAR(255) DEFAULT NULL');
        $this->addSql('ALTER TABLE rule_log ADD CONSTRAINT FK_F8F32F3734128B91 FOREIGN KEY (network_id) REFERENCES network (id)');
        $this->addSql('ALTER TABLE rule_log ADD CONSTRAINT FK_F8F32F3732A83AEB FOREIGN KEY (rule_group_id) REFERENCES rule_group (id)');
        $this->addSql('CREATE INDEX IDX_F8F32F3734128B91 ON rule_log (network_id)');
        $this->addSql('CREATE INDEX IDX_F8F32F3732A83AEB ON rule_log (rule_group_id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE rule_log DROP FOREIGN KEY FK_F8F32F3734128B91');
        $this->addSql('ALTER TABLE rule_log DROP FOREIGN KEY FK_F8F32F3732A83AEB');
        $this->addSql('DROP INDEX IDX_F8F32F3734128B91 ON rule_log');
        $this->addSql('DROP INDEX IDX_F8F32F3732A83AEB ON rule_log');
        $this->addSql('ALTER TABLE rule_log ADD expires_at DATETIME DEFAULT NULL COMMENT \'(DC2Type:datetime_immutable)\', DROP network_id, DROP rule_group_id, CHANGE created_ip created_ip VARCHAR(255) NOT NULL, CHANGE deleted_at deleted_at DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\', CHANGE deleted_ip deleted_ip VARCHAR(255) NOT NULL');
    }
}
