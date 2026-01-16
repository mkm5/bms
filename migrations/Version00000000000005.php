<?php declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version00000000000005 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'form definition, form field';
    }

    public function up(Schema $schema): void
    {
        $this->addSql(<<<SQL
        CREATE TABLE form_definition (
            id SERIAL PRIMARY KEY,
            project_id INT NOT NULL,
            name VARCHAR(255) NOT NULL,
            description TEXT DEFAULT NULL,
            CONSTRAINT fk_form_definition_project FOREIGN KEY (project_id) REFERENCES project (id) ON DELETE CASCADE
        )
        SQL);
        $this->addSql('CREATE INDEX idx_form_definition_project ON form_definition (project_id)');

        $this->addSql(<<<SQL
        CREATE TABLE form_field (
            id SERIAL PRIMARY KEY,
            form_definition_id INT NOT NULL,
            name VARCHAR(255) NOT NULL,
            label VARCHAR(255) NOT NULL,
            help_text TEXT DEFAULT NULL,
            is_required BOOLEAN NOT NULL DEFAULT FALSE,
            type VARCHAR(20) NOT NULL,
            options JSONB NOT NULL,
            display_order INT NOT NULL DEFAULT 0,
            CONSTRAINT fk_form_field_form_definition FOREIGN KEY (form_definition_id) REFERENCES form_definition (id) ON DELETE CASCADE
        )
        SQL);
        $this->addSql('CREATE INDEX idx_form_field_form_definition ON form_field (form_definition_id)');
        $this->addSql('CREATE INDEX idx_form_field_display_order ON form_field (display_order)');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('DROP TABLE form_field');
        $this->addSql('DROP TABLE form_definition');
    }
}
