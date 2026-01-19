<?php declare(strict_types=1);

namespace App\Entity;

use App\Config\StorageType;
use App\Repository\DocumentVersionRepository;
use DateTimeImmutable;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use InvalidArgumentException;

#[ORM\Entity(repositoryClass: DocumentVersionRepository::class)]
#[ORM\Index(name: 'IDX_DOCUMENT_VERSION_DOCUMENT', columns: ['document_id'])]
#[ORM\UniqueConstraint(name: 'DOCUMENT_VERSION_UNIQ_DOC_VERSION', fields: ['document', 'versionNumber'])]
class DocumentVersion
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\ManyToOne(targetEntity: Document::class, inversedBy: 'versions')]
    #[ORM\JoinColumn(name: 'document_id', nullable: false, onDelete: 'CASCADE')]
    private ?Document $document = null;

    #[ORM\OneToOne(targetEntity: File::class, cascade: ['persist', 'remove'], orphanRemoval: true, fetch: 'EAGER')]
    #[ORM\JoinColumn(name: 'file_id', nullable: false, onDelete: 'CASCADE')]
    private File $file;

    #[ORM\Column(type: Types::INTEGER)]
    private int $versionNumber;

    #[ORM\Column(type: Types::TEXT, nullable: true)]
    private ?string $note = null;

    #[ORM\Column(type: Types::DATETIME_IMMUTABLE)]
    private DateTimeImmutable $createdAt;

    public function __construct()
    {
        $this->createdAt = new DateTimeImmutable();
    }

    public static function create(
        Document $document,
        File $file,
        ?string $note = null,
    ): self {
        if ($file->getStorage() !== StorageType::DOCUMENTS) {
            throw new InvalidArgumentException('Document version file must use DOCUMENTS storage type');
        }

        $version = (new self())
            ->setFile($file)
            ->setNote($note)
            ->setVersionNumber($document->getNextVersionNumber())
        ;

        $document->addVersion($version);

        return $version;
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getDocument(): ?Document
    {
        return $this->document;
    }

    public function setDocument(?Document $document): self
    {
        $this->document = $document;
        return $this;
    }

    public function getFile(): File
    {
        return $this->file;
    }

    public function setFile(File $file): self
    {
        $this->file = $file;
        return $this;
    }

    public function getVersionNumber(): int
    {
        return $this->versionNumber;
    }

    public function setVersionNumber(int $versionNumber): self
    {
        $this->versionNumber = $versionNumber;
        return $this;
    }

    public function getNote(): ?string
    {
        return $this->note;
    }

    public function setNote(?string $note): self
    {
        $this->note = $note;
        return $this;
    }

    public function getCreatedAt(): DateTimeImmutable
    {
        return $this->createdAt;
    }

    public function getOriginalFileName(): string
    {
        return $this->file->getOriginalFileName();
    }

    public function getMimeType(): ?string
    {
        return $this->file->getMimeType();
    }
}
