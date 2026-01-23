<?php declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version00000000000012 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Company addres and note';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('ALTER TABLE company ADD address TEXT DEFAULT NULL');
        $this->addSql('ALTER TABLE company ADD note TEXT DEFAULT NULL');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE company DROP address');
        $this->addSql('ALTER TABLE company DROP note');
    }
}
