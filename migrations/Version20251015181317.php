<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20251015181317 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE network DROP FOREIGN KEY FK_608487BCF702BF4F');
        $this->addSql('ALTER TABLE active_rule DROP FOREIGN KEY FK_A8B83E1C32A83AEB');
        $this->addSql('ALTER TABLE active_rule DROP FOREIGN KEY FK_A8B83E1CB03A8386');
        $this->addSql('DROP TABLE active_rule');
        $this->addSql('DROP INDEX UNIQ_608487BCF702BF4F ON network');
        $this->addSql('ALTER TABLE network ADD enabled_by_id INT DEFAULT NULL, ADD enabled_at DATETIME DEFAULT NULL COMMENT \'(DC2Type:datetime_immutable)\', ADD expires_at DATETIME DEFAULT NULL COMMENT \'(DC2Type:datetime_immutable)\', ADD enabled_ip VARCHAR(255) DEFAULT NULL, CHANGE active_rule_id rule_group_id INT DEFAULT NULL');
        $this->addSql('ALTER TABLE network ADD CONSTRAINT FK_608487BC32A83AEB FOREIGN KEY (rule_group_id) REFERENCES rule_group (id)');
        $this->addSql('ALTER TABLE network ADD CONSTRAINT FK_608487BC553BD4CE FOREIGN KEY (enabled_by_id) REFERENCES person (id)');
        $this->addSql('CREATE INDEX IDX_608487BC32A83AEB ON network (rule_group_id)');
        $this->addSql('CREATE INDEX IDX_608487BC553BD4CE ON network (enabled_by_id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE active_rule (id INT AUTO_INCREMENT NOT NULL, rule_group_id INT NOT NULL, created_by_id INT DEFAULT NULL, created_at DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\', expires_at DATETIME DEFAULT NULL COMMENT \'(DC2Type:datetime_immutable)\', ip VARCHAR(255) CHARACTER SET utf8mb4 NOT NULL COLLATE `utf8mb4_unicode_ci`, INDEX IDX_A8B83E1C32A83AEB (rule_group_id), INDEX IDX_A8B83E1CB03A8386 (created_by_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB COMMENT = \'\' ');
        $this->addSql('ALTER TABLE active_rule ADD CONSTRAINT FK_A8B83E1C32A83AEB FOREIGN KEY (rule_group_id) REFERENCES rule_group (id) ON UPDATE NO ACTION ON DELETE NO ACTION');
        $this->addSql('ALTER TABLE active_rule ADD CONSTRAINT FK_A8B83E1CB03A8386 FOREIGN KEY (created_by_id) REFERENCES person (id) ON UPDATE NO ACTION ON DELETE NO ACTION');
        $this->addSql('ALTER TABLE network DROP FOREIGN KEY FK_608487BC32A83AEB');
        $this->addSql('ALTER TABLE network DROP FOREIGN KEY FK_608487BC553BD4CE');
        $this->addSql('DROP INDEX IDX_608487BC32A83AEB ON network');
        $this->addSql('DROP INDEX IDX_608487BC553BD4CE ON network');
        $this->addSql('ALTER TABLE network ADD active_rule_id INT DEFAULT NULL, DROP rule_group_id, DROP enabled_by_id, DROP enabled_at, DROP expires_at, DROP enabled_ip');
        $this->addSql('ALTER TABLE network ADD CONSTRAINT FK_608487BCF702BF4F FOREIGN KEY (active_rule_id) REFERENCES active_rule (id) ON UPDATE NO ACTION ON DELETE NO ACTION');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_608487BCF702BF4F ON network (active_rule_id)');
    }
}
