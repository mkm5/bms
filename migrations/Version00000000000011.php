<?php declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version00000000000011 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Contact, note and address';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('ALTER TABLE contact ADD address TEXT DEFAULT NULL');
        $this->addSql('ALTER TABLE contact ADD note TEXT DEFAULT NULL');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE contact DROP address');
        $this->addSql('ALTER TABLE contact DROP note');
    }
}
