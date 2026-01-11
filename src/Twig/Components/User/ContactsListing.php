<?php declare(strict_types=1);

namespace App\Twig\Components\User;

use App\Entity\Contact;
use App\Repository\ContactRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\UX\LiveComponent\Attribute\AsLiveComponent;
use Symfony\UX\LiveComponent\Attribute\LiveAction;
use Symfony\UX\LiveComponent\Attribute\LiveArg;
use Symfony\UX\LiveComponent\Attribute\LiveListener;
use Symfony\UX\LiveComponent\Attribute\LiveProp;
use Symfony\UX\LiveComponent\ComponentToolsTrait;
use Symfony\UX\LiveComponent\DefaultActionTrait;
use App\Service\Paginator;
use App\Service\PaginatorTrait;
use Symfony\UX\LiveComponent\Metadata\UrlMapping;

#[AsLiveComponent]
final class ContactsListing extends AbstractController
{
    public const MODAL_NAME = 'contact';

    use ComponentToolsTrait;
    use DefaultActionTrait;
    use PaginatorTrait;

    #[LiveProp(writable: true, url: new UrlMapping(as: 'q'))]
    public ?string $search = null;

    #[LiveProp(writable: true, url: true)]
    public int $page = 1;

    private const PER_PAGE = 20;

    private ?Paginator $paginator = null;

    public function __construct(
        private readonly ContactRepository $contactRepository,
        private readonly EntityManagerInterface $em,
    ) {
    }

    #[LiveListener('contact:update')]
    public function onContactUpdate(#[LiveArg] ?int $contact = null): void
    {
    }

    #[LiveAction]
    public function editContact(#[LiveArg] ?int $contactId = null): void
    {
        $this->emit('contact:edit', ['contact' => $contactId]);
    }

    private function countContacts(): int
    {
        return $this->contactRepository->countSearch(
            search: $this->search,
        );
    }

    /**
     * @return Contact[]
     */
    private function fetchContacts(int $limit, int $offset): array
    {
        return $this->contactRepository->search(
            search: $this->search,
            limit: $limit,
            offset: $offset,
        );
    }

    /**
     * @return Paginator<Contact>
     */
    protected function getPaginator(): Paginator
    {
        if ($this->paginator === null) {
            $this->paginator = new Paginator(
                currentPage: $this->page,
                itemsPerPage: self::PER_PAGE,
                countCallback: $this->countContacts(...),
                fetchCallback: $this->fetchContacts(...),
            );
        }

        return $this->paginator;
    }

    /**
     * @return Contact[]
     */
    public function getContacts(): array
    {
        return $this->getItems();
    }

    public function getTotalContacts(): int
    {
        return $this->getTotalItems();
    }

    public function getModalName(): string
    {
        return self::MODAL_NAME;
    }
}
