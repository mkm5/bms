<?php declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version00000000000018 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Fix for form FK';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('ALTER TABLE form_field DROP CONSTRAINT fk_d8b2e19b72c8ee2e');
        $this->addSql('ALTER TABLE form_field ADD CONSTRAINT FK_D8B2E19B72C8EE2E FOREIGN KEY (form_definition_id) REFERENCES form_definition (id) ON DELETE CASCADE NOT DEFERRABLE');
        $this->addSql('ALTER TABLE form_submission DROP CONSTRAINT fk_d2c216675ff69b7d');
        $this->addSql('ALTER TABLE form_submission ADD CONSTRAINT FK_D2C216675FF69B7D FOREIGN KEY (form_id) REFERENCES form_definition (id) ON DELETE CASCADE NOT DEFERRABLE');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE form_field DROP CONSTRAINT FK_D8B2E19B72C8EE2E');
        $this->addSql('ALTER TABLE form_field ADD CONSTRAINT fk_d8b2e19b72c8ee2e FOREIGN KEY (form_definition_id) REFERENCES form_definition (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE form_submission DROP CONSTRAINT FK_D2C216675FF69B7D');
        $this->addSql('ALTER TABLE form_submission ADD CONSTRAINT fk_d2c216675ff69b7d FOREIGN KEY (form_id) REFERENCES form_definition (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
    }
}
