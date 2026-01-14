<?php declare(strict_types=1);

namespace App\Entity;

use App\Repository\TicketRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: TicketRepository::class)]
class Ticket
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\ManyToOne(inversedBy: 'tickets')]
    #[ORM\JoinColumn(nullable: false)]
    private ?Project $project = null;

    #[ORM\Column(length: 255)]
    private ?string $title = null;

    #[ORM\ManyToOne(inversedBy: 'tickets')]
    #[ORM\JoinColumn(nullable: false)]
    private ?TicketStatus $status = null;

    #[ORM\Column(type: Types::TEXT, nullable: true)]
    private ?string $description = null;

    /**
     * @var Collection<int, Tag>
     */
    #[ORM\ManyToMany(targetEntity: Tag::class)]
    private Collection $tags;

    /**
     * @var Collection<int, TicketTask>
     */
    #[ORM\OneToMany(targetEntity: TicketTask::class, mappedBy: 'ticket', orphanRemoval: true, cascade: ['persist'])]
    private Collection $tasks;

    #[ORM\Column(options: ['default' => 0])]
    private int $displayOrder = 0;

    public function __construct()
    {
        $this->tags = new ArrayCollection();
        $this->tasks = new ArrayCollection();
    }

    public static function create(
        Project $project,
        string $title,
        TicketStatus $ticketStatus,
        ?string $description,
        ?array $tags = null,
    ): Ticket
    {
        $ticket = (new self)
            ->setProject($project)
            ->setTitle($title)
            ->setStatus($ticketStatus)
            ->setDescription($description)
        ;

        foreach ($tags as $tag) {
            $ticket->addTag($tag);
        }

        return $ticket;
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getProject(): Project
    {
        return $this->project;
    }

    public function setProject(?Project $project): self
    {
        $this->project = $project;
        return $this;
    }

    public function getTitle(): ?string
    {
        return $this->title;
    }

    public function setTitle(string $title): self
    {
        $this->title = $title;
        return $this;
    }

    public function getStatus(): ?TicketStatus
    {
        return $this->status;
    }

    public function setStatus(?TicketStatus $status): self
    {
        $this->status = $status;
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

    /**
     * @return Collection<int, Tag>
     */
    public function getTags(): Collection
    {
        return $this->tags;
    }

    public function addTag(Tag $tag): static
    {
        if (!$this->tags->contains($tag)) {
            $this->tags->add($tag);
        }

        return $this;
    }

    public function removeTag(Tag $tag): self
    {
        $this->tags->removeElement($tag);
        return $this;
    }

    /**
     * @return Collection<int, TicketTask>
     */
    public function getTasks(): Collection
    {
        return $this->tasks;
    }

    public function addTask(TicketTask $task): self
    {
        if (!$this->tasks->contains($task)) {
            $this->tasks->add($task);
            $task->setTicket($this);
        }

        return $this;
    }

    public function removeTask(TicketTask $task): self
    {
        if ($this->tasks->removeElement($task)) {
            // set the owning side to null (unless already changed)
            if ($task->getTicket() === $this) {
                $task->setTicket(null);
            }
        }

        return $this;
    }

    public function getDisplayOrder(): ?int
    {
        return $this->displayOrder;
    }

    public function setDisplayOrder(int $displayOrder): self
    {
        $this->displayOrder = $displayOrder;
        return $this;
    }
}
