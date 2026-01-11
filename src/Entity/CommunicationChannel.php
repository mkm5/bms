<?php declare(strict_types=1);

namespace App\Entity;

use App\Config\CommunicationType;
use App\Repository\CommunicationChannelRepository;
use Doctrine\ORM\Mapping as ORM;
use InvalidArgumentException;

#[ORM\Entity(repositoryClass: CommunicationChannelRepository::class)]
class CommunicationChannel
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 255, enumType: CommunicationType::class)]
    private ?CommunicationType $type = null;

    #[ORM\Column(length: 255)]
    private ?string $value = null;

    #[ORM\ManyToOne(inversedBy: 'communcationChannels')]
    #[ORM\JoinColumn(nullable: false)]
    private ?Contact $contact = null;

    public static function create(
        CommunicationType $type,
        string $value,
        Contact $contact,
    ): CommunicationChannel
    {
        if (empty(trim($value))) {
            throw new InvalidArgumentException('Communcation channel must have value');
        }

        return (new self)
            ->setType($type)
            ->setValue($value)
            ->setContact($contact)
        ;
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getType(): ?CommunicationType
    {
        return $this->type;
    }

    public function setType(CommunicationType $type): self
    {
        $this->type = $type;
        return $this;
    }

    public function getValue(): ?string
    {
        return $this->value;
    }

    public function setValue(string $value): self
    {
        $this->value = $value;
        return $this;
    }

    public function getContact(): ?Contact
    {
        return $this->contact;
    }

    public function setContact(?Contact $contact): self
    {
        $this->contact = $contact;
        return $this;
    }
}
