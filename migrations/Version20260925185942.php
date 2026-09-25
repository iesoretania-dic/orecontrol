<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20260925185942 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE scheduled_task (id INT AUTO_INCREMENT NOT NULL, target_rule_group_id INT DEFAULT NULL, created_by_id INT DEFAULT NULL, name VARCHAR(255) NOT NULL, enabled TINYINT(1) NOT NULL, all_networks TINYINT(1) NOT NULL, weekdays JSON NOT NULL, time TIME NOT NULL COMMENT \'(DC2Type:time_immutable)\', last_run_at DATETIME DEFAULT NULL COMMENT \'(DC2Type:datetime_immutable)\', INDEX IDX_9DBF9C0C2E24095A (target_rule_group_id), INDEX IDX_9DBF9C0CB03A8386 (created_by_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE scheduled_task_network (scheduled_task_id INT NOT NULL, network_id INT NOT NULL, INDEX IDX_A9012DC7E97157C2 (scheduled_task_id), INDEX IDX_A9012DC734128B91 (network_id), PRIMARY KEY(scheduled_task_id, network_id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('ALTER TABLE scheduled_task ADD CONSTRAINT FK_9DBF9C0C2E24095A FOREIGN KEY (target_rule_group_id) REFERENCES rule_group (id)');
        $this->addSql('ALTER TABLE scheduled_task ADD CONSTRAINT FK_9DBF9C0CB03A8386 FOREIGN KEY (created_by_id) REFERENCES person (id)');
        $this->addSql('ALTER TABLE scheduled_task_network ADD CONSTRAINT FK_A9012DC7E97157C2 FOREIGN KEY (scheduled_task_id) REFERENCES scheduled_task (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE scheduled_task_network ADD CONSTRAINT FK_A9012DC734128B91 FOREIGN KEY (network_id) REFERENCES network (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE person CHANGE external external TINYINT(1) NOT NULL, CHANGE active active TINYINT(1) NOT NULL');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE scheduled_task DROP FOREIGN KEY FK_9DBF9C0C2E24095A');
        $this->addSql('ALTER TABLE scheduled_task DROP FOREIGN KEY FK_9DBF9C0CB03A8386');
        $this->addSql('ALTER TABLE scheduled_task_network DROP FOREIGN KEY FK_A9012DC7E97157C2');
        $this->addSql('ALTER TABLE scheduled_task_network DROP FOREIGN KEY FK_A9012DC734128B91');
        $this->addSql('DROP TABLE scheduled_task');
        $this->addSql('DROP TABLE scheduled_task_network');
        $this->addSql('ALTER TABLE person CHANGE external external TINYINT(1) DEFAULT 0 NOT NULL, CHANGE active active TINYINT(1) DEFAULT 1 NOT NULL');
    }
}
