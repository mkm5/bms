<?php declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version00000000000013 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Adding `pg_trgm` extension. Form submission and document search data';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('CREATE EXTENSION IF NOT EXISTS pg_trgm');

        $this->addSql(<<<SQL
        CREATE OR REPLACE FUNCTION jsonb_array_to_string(input jsonb, delim text)
        RETURNS text
        LANGUAGE sql
        IMMUTABLE
        AS $$
            SELECT string_agg(elem, delim) FROM jsonb_array_elements_text(input) AS elem
        $$;
        SQL);

        $this->addSql('DROP INDEX IF EXISTS idx_form_submission_search_data');
        $this->addSql(<<<SQL
        ALTER TABLE form_submission
            DROP COLUMN search_data,
            ADD COLUMN search_data text GENERATED ALWAYS AS (
                jsonb_array_to_string(jsonb_path_query_array(data, '$.*[*]'), ' ')
            ) STORED
        SQL);
        // SELECT string_agg(value, ', ') FROM jsonb_array_elements_text(jsonb_path_query_array(data, '$.*[*]'))
        $this->addSql('CREATE INDEX idx_form_submission_search_data ON form_submission USING GIN (search_data gin_trgm_ops)');

        $this->addSql('DROP INDEX IF EXISTS idx_document_search_data');
        $this->addSql(<<<SQL
        ALTER TABLE document
            DROP COLUMN search_data,
            ADD COLUMN search_data text GENERATED ALWAYS AS (
                COALESCE(name, '') || ' ' || COALESCE(description, '')
            ) STORED
        SQL);
        $this->addSql('CREATE INDEX idx_document_search_data ON document USING GIN (search_data gin_trgm_ops)');
    }

    public function down(Schema $schema): void
    {
    }
}
