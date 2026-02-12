<?php declare(strict_types=1);

namespace App\Entity;

use App\Config\FormFieldType;
use App\Repository\FormFieldRepository;
use DateTimeImmutable;
use DateTimeInterface;
use DateTimeZone;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity(repositoryClass: FormFieldRepository::class)]
class FormField
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    #[Assert\NotBlank]
    private ?string $name = null;

    #[ORM\Column(length: 255)]
    private ?string $label = null;

    #[ORM\Column(type: Types::TEXT, nullable: true)]
    private ?string $helpText = null;

    #[ORM\Column]
    private bool $isRequired = false;

    #[ORM\Column(length: 20, enumType: FormFieldType::class)]
    private FormFieldType $type = FormFieldType::TEXT;

    #[ORM\Column(type: Types::JSON, options: ['jsonb' => true])]
    private array $options = [];

    #[ORM\Column]
    private int $displayOrder = 0;

    #[ORM\ManyToOne(inversedBy: 'fields')]
    #[ORM\JoinColumn(nullable: false, onDelete: 'CASCADE')]
    private ?FormDefinition $formDefinition = null;

    public static function create(
        string $name,
        FormFieldType $type,
        ?string $helpText = null,
        ?bool $isRequired = false,
        array $options = [],
    ): FormField
    {
        return (new self)
            ->setName($name)
            ->setType($type)
            ->setHelpText($helpText)
            ->setIsRequired($isRequired)
            ->setOptions($options)
        ;
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getName(): ?string
    {
        return $this->name;
    }

    public function setName(string $name): self
    {
        $this->name = $name;
        $this->updateLabel();
        return $this;
    }

    public function getLabel(): ?string
    {
        return $this->label;
    }

    public function setLabel(string $label): self
    {
        $this->label = $label;
        return $this;
    }

    public function getHelpText(): ?string
    {
        return $this->helpText;
    }

    public function setHelpText(?string $helpText): self
    {
        $this->helpText = $helpText;
        return $this;
    }

    public function isRequired(): bool
    {
        return $this->isRequired;
    }

    public function setIsRequired(bool $isRequired): self
    {
        $this->isRequired = $isRequired;
        return $this;
    }

    public function isType(FormFieldType $type): bool
    {
        return $this->type === $type;
    }

    public function getType(): FormFieldType
    {
        return $this->type;
    }

    public function setType(FormFieldType $type): self
    {
        $this->type = $type;
        return $this;
    }

    public function getOptions(): array
    {
        return $this->options;
    }

    public function setOptions(array $options): self
    {
        $this->options = $options;
        return $this;
    }

    public function getDisplayOrder(): int
    {
        return $this->displayOrder;
    }

    public function setDisplayOrder(int $displayOrder): self
    {
        $this->displayOrder = $displayOrder;
        return $this;
    }

    public function getFormDefinition(): ?FormDefinition
    {
        return $this->formDefinition;
    }

    public function setFormDefinition(?FormDefinition $formDefinition): self
    {
        $this->formDefinition = $formDefinition;
        return $this;
    }

    private function updateLabel(): void
    {
        if ($this->name === null) {
            $this->label = null;
            return;
        }

        $this->label = strtolower(trim(preg_replace('/[^a-zA-Z0-9]+/', '_', $this->name), '_'));
    }

    public function displayValue(mixed $value): ?string
    {
        return match($this->type) {
            FormFieldType::CHECKBOX => $value ? 'Yes' : 'No',
            FormFieldType::DATE => $this->valueToDateTime($value)->format('Y-m-d'),
            FormFieldType::TIME => $this->valueToDateTime($value)->format('H:i'),
            FormFieldType::DATETIME => $this->valueToDateTime($value)->format('Y-m-d H:i'),
            default => $value === null
                ? null : ((is_array($value)) ? implode(', ', $value) : (string)$value),
        };
    }

    private function valueToDateTime(mixed $value): DateTimeImmutable
    {
        return new DateTimeImmutable($value['date'], new DateTimeZone($value['timezone']));
    }
}
