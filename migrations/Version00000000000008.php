<?php declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version00000000000008 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Add full text tsvector based search.';
    }

    public function up(Schema $schema): void
    {
        /**
         * NOTE:
         * Using "simple" changes from linguistic based search to tokenized one.
         * Which means that searching for "running" will only search for "run"
         * and not "ran", "running" nor "runnable".
         */
        $this->addSql(<<<SQL
        ALTER TABLE form_submission
        ADD COLUMN search_data tsvector
        GENERATED ALWAYS AS (
            to_tsvector('simple', jsonb_path_query_array(data, '$.**')::text)
            || to_tsvector('english', jsonb_path_query_array(data, '$.**')::text)
        ) STORED;
        SQL);

        $this->addSql('CREATE INDEX idx_form_submission_search_data ON form_submission USING GIN (search_data);');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('DROP INDEX idx_form_submission_search_data;');
        $this->addSql('ALTER TABLE form_submission DROP search_data;');
    }
}
