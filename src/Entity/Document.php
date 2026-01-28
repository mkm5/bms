<?php declare(strict_types=1);

namespace App\Entity;

use App\Repository\DocumentRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use InvalidArgumentException;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity(repositoryClass: DocumentRepository::class)]
#[ORM\Index(name: 'idx_document_search_data', columns: ['search_data'])]
class Document
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    #[Assert\NotBlank]
    private string $name;

    #[ORM\Column(type: Types::TEXT, nullable: true)]
    private ?string $description = null;

    #[ORM\OneToOne(targetEntity: DocumentVersion::class)]
    #[ORM\JoinColumn(name: 'current_version_id', nullable: true, onDelete: 'SET NULL')]
    private ?DocumentVersion $currentVersion = null;

    /** @var Collection<int, DocumentVersion> */
    #[ORM\OneToMany(targetEntity: DocumentVersion::class, mappedBy: 'document', cascade: ['persist', 'remove'], orphanRemoval: true)]
    #[ORM\OrderBy(['versionNumber' => 'DESC'])]
    private Collection $versions;

    #[ORM\Column(
        type: Types::TEXT,
        nullable: true,
        insertable: false,
        updatable: false,
        generated: 'ALWAYS',
    )]
    private ?string $searchData = null;

    public function __construct()
    {
        $this->versions = new ArrayCollection();
    }

    public static function create(string $name, ?string $description = null): self
    {
        if (empty(trim($name))) {
            throw new InvalidArgumentException('Document must have a name');
        }

        return (new self())
            ->setName($name)
            ->setDescription($description)
        ;
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function setName(string $name): self
    {
        $this->name = $name;
        return $this;
    }

    public function getDescription(): ?string
    {
        return $this->description;
    }

    public function setDescription(?string $description): self
    {
        $this->description = $description;
        return $this;
    }

    public function getCurrentVersion(): ?DocumentVersion
    {
        return $this->currentVersion;
    }

    public function setCurrentVersion(?DocumentVersion $currentVersion): self
    {
        $this->currentVersion = $currentVersion;
        return $this;
    }

    /** @return Collection<int, DocumentVersion> */
    public function getVersions(): Collection
    {
        return $this->versions;
    }

    public function getNextVersionNumber(): int
    {
        if ($this->versions->isEmpty()) {
            return 1;
        }

        return $this->versions->first()->getVersionNumber() + 1;
    }

    public function addVersion(DocumentVersion $version): self
    {
        if (!$this->versions->contains($version)) {
            $this->versions->add($version);
            $version->setDocument($this);
            $this->setCurrentVersion($version);
        }

        return $this;
    }

    public function removeVersion(DocumentVersion $version): self
    {
        if ($this->versions->removeElement($version)) {
            if ($version->getDocument() === $this) {
                $version->setDocument(null);
            }

            if ($this->currentVersion === $version) {
                $this->currentVersion = $this->versions->isEmpty() ? null : $this->versions->first();
            }
        }

        return $this;
    }
}
