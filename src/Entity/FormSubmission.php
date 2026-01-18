<?php declare(strict_types=1);

namespace App\Entity;

use App\Repository\FormSubmissionRepository;
use DateTimeImmutable;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: FormSubmissionRepository::class)]
#[ORM\Index(name: 'idx_form_submission_search_data', columns: ['search_data'])]
class FormSubmission
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\ManyToOne]
    #[ORM\JoinColumn(nullable: false)]
    private ?FormDefinition $form = null;

    #[ORM\Column(type: Types::JSON, options: ['jsonb' => true])]
    private array $data = [];

    #[ORM\Column(
        type: Types::TEXT,
        nullable: true,
        columnDefinition: 'tsvector',
        insertable: false,
        updatable: false,
    )]
    private $searchData = null;

    #[ORM\Column]
    private DateTimeImmutable $submittedAt;

    public function __construct()
    {
        $this->submittedAt = new DateTimeImmutable();
    }

    public static function create(FormDefinition $form, array $data): FormSubmission
    {
        return (new self)
            ->setFormDefinition($form)
            ->setData($data)
        ;
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getForm(): ?FormDefinition
    {
        return $this->form;
    }

    public function setFormDefinition(?FormDefinition $form): self
    {
        $this->form = $form;
        return $this;
    }

    public function getData(): array
    {
        return $this->data;
    }

    public function setData(array $data): self
    {
        $this->data = $data;
        return $this;
    }

    public function getSubmittedAt(): DateTimeImmutable
    {
        return $this->submittedAt;
    }

    public function setSubmittedAt(DateTimeImmutable $submittedAt): self
    {
        $this->submittedAt = $submittedAt;
        return $this;
    }
}
