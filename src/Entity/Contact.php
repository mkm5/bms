<?php declare(strict_types=1);

namespace App\Entity;

use App\Config\CommunicationType;
use App\Repository\ContactRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use InvalidArgumentException;

#[ORM\Entity(repositoryClass: ContactRepository::class)]
class Contact
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    private ?string $firstName = null;

    #[ORM\Column(length: 255)]
    private ?string $lastName = null;

    #[ORM\Column(type: Types::TEXT, nullable: true)]
    private ?string $address = null;

    #[ORM\Column(type: Types::TEXT, nullable: true)]
    private ?string $note = null;

    /** @var Collection<int, CommunicationChannel> */
    #[ORM\OneToMany(targetEntity: CommunicationChannel::class, mappedBy: 'contact', cascade: ['persist'], orphanRemoval: true)]
    private Collection $communcationChannels;

    #[ORM\ManyToOne(inversedBy: 'contacts')]
    private ?Company $company = null;

    public function __construct()
    {
        $this->communcationChannels = new ArrayCollection();
    }

    public static function create(
        string $firstName,
        string $lastName,
        ?Company $company = null
    ): Contact
    {
        if (empty(trim($firstName)) || empty(trim($lastName))) {
            throw new InvalidArgumentException('Contact must have first and last name');
        }

        return (new self)
            ->setFirstName($firstName)
            ->setLastName($lastName)
            ->setCompany($company)
        ;
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getFirstName(): string
    {
        return $this->firstName;
    }

    public function setFirstName(string $firstName): static
    {
        $this->firstName = $firstName;
        return $this;
    }

    public function getLastName(): string
    {
        return $this->lastName;
    }

    public function setLastName(string $lastName): self
    {
        $this->lastName = $lastName;
        return $this;
    }

    public function getAddress(): ?string
    {
        return $this->address;
    }

    public function setAddress(?string $address): self
    {
        $this->address = $address;
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

    /** @return Collection<int, CommunicationChannel> */
    public function getCommuncationChannels(): Collection
    {
        return $this->communcationChannels;
    }

    public function addCommuncationChannel(CommunicationChannel $communcationChannel): self
    {
        if (!$this->communcationChannels->contains($communcationChannel)) {
            $this->communcationChannels->add($communcationChannel);
            $communcationChannel->setContact($this);
        }
        return $this;
    }

    public function removeCommuncationChannel(CommunicationChannel $communcationChannel): self
    {
        if ($this->communcationChannels->removeElement($communcationChannel)) {
            // set the owning side to null (unless already changed)
            if ($communcationChannel->getContact() === $this) {
                $communcationChannel->setContact(null);
            }
        }
        return $this;
    }

    public function withCommuncationChannel(CommunicationType $type, string $value): self
    {
        return $this->addCommuncationChannel(CommunicationChannel::create($type, $value, $this));
    }

    public function withEmail(string $email): self
    {
        return $this->withCommuncationChannel(CommunicationType::EMAIL, $email);
    }

    public function withWorkPhone(string $phone): self
    {
        return $this->withCommuncationChannel(CommunicationType::PHONE_WORK, $phone);
    }

    public function withPersonalPhone(string $phone): self
    {
        return $this->withCommuncationChannel(CommunicationType::PHONE_PERSONAL, $phone);
    }

    public function getCompany(): ?Company
    {
        return $this->company;
    }

    public function setCompany(?Company $company): static
    {
        $this->company = $company;
        return $this;
    }
}
