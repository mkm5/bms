<?php declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version00000000000019 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Adding `is_archived` flag to tickets';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('ALTER TABLE form_definition ALTER status DROP DEFAULT');
        $this->addSql('ALTER TABLE ticket ADD is_archived BOOLEAN DEFAULT false NOT NULL');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE form_definition ALTER status SET DEFAULT 0');
        $this->addSql('ALTER TABLE ticket DROP is_archived');
    }
}
