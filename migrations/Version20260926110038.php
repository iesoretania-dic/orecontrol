<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20260926110038 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE rule_log ADD created_scheduled_task_id INT DEFAULT NULL, ADD deleted_scheduled_task_id INT DEFAULT NULL, ADD created_via_scheduled_task TINYINT(1) NOT NULL, ADD deleted_via_scheduled_task TINYINT(1) NOT NULL');
        $this->addSql('ALTER TABLE rule_log ADD CONSTRAINT FK_F8F32F379E13B6E2 FOREIGN KEY (created_scheduled_task_id) REFERENCES scheduled_task (id) ON DELETE SET NULL');
        $this->addSql('ALTER TABLE rule_log ADD CONSTRAINT FK_F8F32F37D95B38F2 FOREIGN KEY (deleted_scheduled_task_id) REFERENCES scheduled_task (id) ON DELETE SET NULL');
        $this->addSql('CREATE INDEX IDX_F8F32F379E13B6E2 ON rule_log (created_scheduled_task_id)');
        $this->addSql('CREATE INDEX IDX_F8F32F37D95B38F2 ON rule_log (deleted_scheduled_task_id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE rule_log DROP FOREIGN KEY FK_F8F32F379E13B6E2');
        $this->addSql('ALTER TABLE rule_log DROP FOREIGN KEY FK_F8F32F37D95B38F2');
        $this->addSql('DROP INDEX IDX_F8F32F379E13B6E2 ON rule_log');
        $this->addSql('DROP INDEX IDX_F8F32F37D95B38F2 ON rule_log');
        $this->addSql('ALTER TABLE rule_log DROP created_scheduled_task_id, DROP deleted_scheduled_task_id, DROP created_via_scheduled_task, DROP deleted_via_scheduled_task');
    }
}
