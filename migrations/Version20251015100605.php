<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20251015100605 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE active_rule DROP FOREIGN KEY FK_A8B83E1C34128B91');
        $this->addSql('DROP INDEX IDX_A8B83E1C34128B91 ON active_rule');
        $this->addSql('ALTER TABLE active_rule DROP network_id');
        $this->addSql('ALTER TABLE network ADD active_rule_id INT DEFAULT NULL');
        $this->addSql('ALTER TABLE network ADD CONSTRAINT FK_608487BCF702BF4F FOREIGN KEY (active_rule_id) REFERENCES active_rule (id)');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_608487BCF702BF4F ON network (active_rule_id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE active_rule ADD network_id INT NOT NULL');
        $this->addSql('ALTER TABLE active_rule ADD CONSTRAINT FK_A8B83E1C34128B91 FOREIGN KEY (network_id) REFERENCES network (id) ON UPDATE NO ACTION ON DELETE NO ACTION');
        $this->addSql('CREATE INDEX IDX_A8B83E1C34128B91 ON active_rule (network_id)');
        $this->addSql('ALTER TABLE network DROP FOREIGN KEY FK_608487BCF702BF4F');
        $this->addSql('DROP INDEX UNIQ_608487BCF702BF4F ON network');
        $this->addSql('ALTER TABLE network DROP active_rule_id');
    }
}
