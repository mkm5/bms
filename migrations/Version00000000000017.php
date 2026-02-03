<?php declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version00000000000017 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Adding form status';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('ALTER TABLE form_definition ADD status INT NOT NULL DEFAULT 0');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE form_definition DROP status');
    }
}
