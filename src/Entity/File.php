<?php declare(strict_types=1);

namespace App\Entity;

use App\Config\StorageType;
use App\Repository\FileRepository;
use DateTimeImmutable;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Bridge\Doctrine\Types\UuidType;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Symfony\Component\Uid\Uuid;

use function Symfony\Component\String\u;

#[ORM\Entity(repositoryClass: FileRepository::class)]
#[ORM\Index(name: 'IDX_FILE_STORAGE', columns: ['storage'])]
#[ORM\UniqueConstraint(name: 'FILE_UNIQ_PUBLIC_ID', fields: ['publicId'])]
#[UniqueEntity('publicId')]
class File
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(type: UuidType::NAME, unique: true)]
    private Uuid $publicId;

    #[ORM\Column(enumType: StorageType::class)]
    private StorageType $storage;

    #[ORM\Column(type: Types::DATETIME_IMMUTABLE)]
    private DateTimeImmutable $uploadedAt;

    #[ORM\Column(length: 255)]
    private ?string $originalFileName = null;

    #[ORM\Column(length: 255)]
    private ?string $extension = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $mimeType;

    public function __construct()
    {
        $this->uploadedAt = new DateTimeImmutable();
        $this->publicId = Uuid::v7();
    }

    public static function create(
        StorageType $storage,
        string $originalFileName,
        ?string $extension = null,
        ?string $mimeType = null,
    ): File
    {
        if (empty($extension)) {
            $extension = array_last(explode('.', $$originalFileName));
        }

        return (new self)
            ->setStorage($storage)
            ->setOriginalFileName($originalFileName)
            ->setExtension($extension)
            ->setMimeType($mimeType)
        ;
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getPublicId(): Uuid
    {
        return $this->publicId;
    }

    public function getPublicName(): string
    {
        return $this->publicId . '.' . $this->extension;
    }

    public function getUploadedAt(): DateTimeImmutable
    {
        return $this->uploadedAt;
    }

    public function getStorage(): StorageType
    {
        return $this->storage;
    }

    public function getStorageId(): int
    {
        return $this->storage->value;
    }

    public function getStorageName(): string
    {
        return $this->storage->storageName();
    }

    public function setStorage(StorageType $storage): self
    {
        $this->storage = $storage;
        return $this;
    }

    public function getOriginalFileName(): string
    {
        return $this->originalFileName;
    }

    public function getOriginalFileNameAsciiOnly(): string
    {
        return u($this->originalFileName)->ascii()->toString();
    }

    public function setOriginalFileName(string $originalFileName): self
    {
        $this->originalFileName = $originalFileName;
        return $this;
    }

    public function getExtension(): string
    {
        return $this->extension;
    }

    public function setExtension(string $extension): self
    {
        $this->extension = $extension;
        return $this;
    }

    public function getMimeType(): ?string
    {
        return $this->mimeType;
    }

    public function setMimeType(?string $mimeType): self
    {
        $this->mimeType = $mimeType;
        return $this;
    }
}
