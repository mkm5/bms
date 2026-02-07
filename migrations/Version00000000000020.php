<?php declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version00000000000020 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('ALTER TABLE contact DROP CONSTRAINT fk_4c62e638979b1ad6');
        $this->addSql('ALTER TABLE contact ADD CONSTRAINT FK_4C62E638979B1AD6 FOREIGN KEY (company_id) REFERENCES company (id) ON DELETE CASCADE NOT DEFERRABLE');
        $this->addSql('ALTER TABLE form_definition DROP CONSTRAINT fk_61f7634c166d1f9c');
        $this->addSql('ALTER TABLE form_definition ADD CONSTRAINT FK_61F7634C166D1F9C FOREIGN KEY (project_id) REFERENCES project (id) ON DELETE CASCADE NOT DEFERRABLE');
        $this->addSql('ALTER TABLE ticket DROP CONSTRAINT fk_97a0ada3166d1f9c');
        $this->addSql('ALTER TABLE ticket ADD CONSTRAINT FK_97A0ADA3166D1F9C FOREIGN KEY (project_id) REFERENCES project (id) ON DELETE CASCADE NOT DEFERRABLE');
        $this->addSql('ALTER TABLE ticket_task DROP CONSTRAINT fk_60a5ce1d700047d2');
        $this->addSql('ALTER TABLE ticket_task ADD CONSTRAINT FK_60A5CE1D700047D2 FOREIGN KEY (ticket_id) REFERENCES ticket (id) ON DELETE CASCADE NOT DEFERRABLE');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE contact DROP CONSTRAINT FK_4C62E638979B1AD6');
        $this->addSql('ALTER TABLE contact ADD CONSTRAINT fk_4c62e638979b1ad6 FOREIGN KEY (company_id) REFERENCES company (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE form_definition DROP CONSTRAINT FK_61F7634C166D1F9C');
        $this->addSql('ALTER TABLE form_definition ADD CONSTRAINT fk_61f7634c166d1f9c FOREIGN KEY (project_id) REFERENCES project (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE ticket DROP CONSTRAINT FK_97A0ADA3166D1F9C');
        $this->addSql('ALTER TABLE ticket ADD CONSTRAINT fk_97a0ada3166d1f9c FOREIGN KEY (project_id) REFERENCES project (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE ticket_task DROP CONSTRAINT FK_60A5CE1D700047D2');
        $this->addSql('ALTER TABLE ticket_task ADD CONSTRAINT fk_60a5ce1d700047d2 FOREIGN KEY (ticket_id) REFERENCES ticket (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
    }
}
