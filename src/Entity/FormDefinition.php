<?php declare(strict_types=1);

namespace App\Entity;

use App\Repository\FormDefinitionRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity(repositoryClass: FormDefinitionRepository::class)]
class FormDefinition
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    #[Assert\NotBlank]
    private ?string $name = null;

    #[ORM\Column(type: Types::TEXT, nullable: true)]
    private ?string $description = null;

    #[ORM\ManyToOne]
    #[ORM\JoinColumn(nullable: false)]
    #[Assert\NotNull]
    private ?Project $project = null;

    /** @var Collection<int, FormField> */
    #[ORM\OneToMany(targetEntity: FormField::class, mappedBy: 'formDefinition', orphanRemoval: true, cascade: ['persist', 'remove'])]
    #[ORM\OrderBy(['displayOrder' => 'ASC'])]
    private Collection $fields;

    public function __construct()
    {
        $this->fields = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getName(): ?string
    {
        return $this->name;
    }

    public function setName(?string $name): self
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

    public function getProject(): ?Project
    {
        return $this->project;
    }

    public function setProject(?Project $project): self
    {
        $this->project = $project;
        return $this;
    }

    /** @return Collection<int, FormField> */
    public function getFields(): Collection
    {
        return $this->fields;
    }

    public function addField(FormField $field): self
    {
        if (!$this->fields->contains($field)) {
            $this->fields->add($field);
            $field->setFormDefinition($this);
        }
        return $this;
    }

    public function removeField(FormField $field): self
    {
        if ($this->fields->removeElement($field)) {
            if ($field->getFormDefinition() === $this) {
                $field->setFormDefinition(null);
            }
        }
        return $this;
    }
}
