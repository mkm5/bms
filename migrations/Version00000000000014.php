<?php declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version00000000000014 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Change the way user status is being handled';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('ALTER TABLE "user" ADD status INT NOT NULL DEFAULT 0');
        $this->addSql('UPDATE "user" SET status = 1 WHERE is_active = true AND password IS NOT NULL');
        $this->addSql('UPDATE "user" SET status = 2 WHERE is_active = false AND password IS NOT NULL');
        $this->addSql('ALTER TABLE "user" ALTER status DROP DEFAULT');
        $this->addSql('ALTER TABLE "user" DROP is_active');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE "user" ADD is_active BOOLEAN NOT NULL DEFAULT false');
        $this->addSql('UPDATE "user" SET is_active = true WHERE status = 1');
        $this->addSql('ALTER TABLE "user" ALTER is_active DROP DEFAULT');
        $this->addSql('ALTER TABLE "user" DROP status');
    }
}
