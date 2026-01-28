<?php declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version00000000000015 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Drop tags from documents';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('ALTER TABLE document_tag DROP CONSTRAINT fk_d0234567bad26311');
        $this->addSql('ALTER TABLE document_tag DROP CONSTRAINT fk_d0234567c33f7837');
        $this->addSql('DROP TABLE document_tag');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('CREATE TABLE document_tag (document_id INT NOT NULL, tag_id INT NOT NULL, PRIMARY KEY (document_id, tag_id))');
        $this->addSql('CREATE INDEX idx_d0234567bad26311 ON document_tag (tag_id)');
        $this->addSql('CREATE INDEX idx_d0234567c33f7837 ON document_tag (document_id)');
        $this->addSql('ALTER TABLE document_tag ADD CONSTRAINT fk_d0234567bad26311 FOREIGN KEY (tag_id) REFERENCES tag (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE document_tag ADD CONSTRAINT fk_d0234567c33f7837 FOREIGN KEY (document_id) REFERENCES document (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE');
    }
}
