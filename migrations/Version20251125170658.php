<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20251125170658 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE app_task_items (id BIGSERIAL NOT NULL, task_list_id BIGINT DEFAULT NULL, summary VARCHAR(255) NOT NULL, done BOOLEAN NOT NULL, created TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE INDEX IDX_70A15911224F3C61 ON app_task_items (task_list_id)');
        $this->addSql('COMMENT ON COLUMN app_task_items.created IS \'(DC2Type:datetime_immutable)\'');
        $this->addSql('CREATE TABLE app_task_lists (id BIGSERIAL NOT NULL, owner_id BIGINT NOT NULL, title VARCHAR(255) NOT NULL, archived BOOLEAN NOT NULL, created TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL, last_updated TIMESTAMP(0) WITHOUT TIME ZONE DEFAULT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE INDEX IDX_99992FF97E3C61F9 ON app_task_lists (owner_id)');
        $this->addSql('COMMENT ON COLUMN app_task_lists.created IS \'(DC2Type:datetime_immutable)\'');
        $this->addSql('COMMENT ON COLUMN app_task_lists.last_updated IS \'(DC2Type:datetime_immutable)\'');
        $this->addSql('CREATE TABLE task_list_user (task_list_id BIGINT NOT NULL, user_id BIGINT NOT NULL, PRIMARY KEY(task_list_id, user_id))');
        $this->addSql('CREATE INDEX IDX_B777C4F1224F3C61 ON task_list_user (task_list_id)');
        $this->addSql('CREATE INDEX IDX_B777C4F1A76ED395 ON task_list_user (user_id)');
        $this->addSql('CREATE TABLE app_users (id BIGSERIAL NOT NULL, email VARCHAR(255) NOT NULL, password VARCHAR(255) NOT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_C2502824E7927C74 ON app_users (email)');
        $this->addSql('CREATE TABLE messenger_messages (id BIGSERIAL NOT NULL, body TEXT NOT NULL, headers TEXT NOT NULL, queue_name VARCHAR(190) NOT NULL, created_at TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL, available_at TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL, delivered_at TIMESTAMP(0) WITHOUT TIME ZONE DEFAULT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE INDEX IDX_75EA56E0FB7336F0 ON messenger_messages (queue_name)');
        $this->addSql('CREATE INDEX IDX_75EA56E0E3BD61CE ON messenger_messages (available_at)');
        $this->addSql('CREATE INDEX IDX_75EA56E016BA31DB ON messenger_messages (delivered_at)');
        $this->addSql('COMMENT ON COLUMN messenger_messages.created_at IS \'(DC2Type:datetime_immutable)\'');
        $this->addSql('COMMENT ON COLUMN messenger_messages.available_at IS \'(DC2Type:datetime_immutable)\'');
        $this->addSql('COMMENT ON COLUMN messenger_messages.delivered_at IS \'(DC2Type:datetime_immutable)\'');
        $this->addSql('CREATE OR REPLACE FUNCTION notify_messenger_messages() RETURNS TRIGGER AS $$
            BEGIN
                PERFORM pg_notify(\'messenger_messages\', NEW.queue_name::text);
                RETURN NEW;
            END;
        $$ LANGUAGE plpgsql;');
        $this->addSql('DROP TRIGGER IF EXISTS notify_trigger ON messenger_messages;');
        $this->addSql('CREATE TRIGGER notify_trigger AFTER INSERT OR UPDATE ON messenger_messages FOR EACH ROW EXECUTE PROCEDURE notify_messenger_messages();');
        $this->addSql('ALTER TABLE app_task_items ADD CONSTRAINT FK_70A15911224F3C61 FOREIGN KEY (task_list_id) REFERENCES app_task_lists (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE app_task_lists ADD CONSTRAINT FK_99992FF97E3C61F9 FOREIGN KEY (owner_id) REFERENCES app_users (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE task_list_user ADD CONSTRAINT FK_B777C4F1224F3C61 FOREIGN KEY (task_list_id) REFERENCES app_task_lists (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE task_list_user ADD CONSTRAINT FK_B777C4F1A76ED395 FOREIGN KEY (user_id) REFERENCES app_users (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE SCHEMA public');
        $this->addSql('ALTER TABLE app_task_items DROP CONSTRAINT FK_70A15911224F3C61');
        $this->addSql('ALTER TABLE app_task_lists DROP CONSTRAINT FK_99992FF97E3C61F9');
        $this->addSql('ALTER TABLE task_list_user DROP CONSTRAINT FK_B777C4F1224F3C61');
        $this->addSql('ALTER TABLE task_list_user DROP CONSTRAINT FK_B777C4F1A76ED395');
        $this->addSql('DROP TABLE app_task_items');
        $this->addSql('DROP TABLE app_task_lists');
        $this->addSql('DROP TABLE task_list_user');
        $this->addSql('DROP TABLE app_users');
        $this->addSql('DROP TABLE messenger_messages');
    }
}
