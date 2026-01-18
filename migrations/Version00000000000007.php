<?php declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version00000000000007 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('ALTER TABLE form_submission DROP field_names');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE form_submission ADD field_names JSONB NOT NULL');
    }
}
