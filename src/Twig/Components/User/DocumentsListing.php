<?php declare(strict_types=1);

namespace App\Twig\Components\User;

use App\Entity\Document;
use App\Repository\DocumentRepository;
use App\Service\Paginator;
use App\Service\PaginatorTrait;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\UX\LiveComponent\Attribute\AsLiveComponent;
use Symfony\UX\LiveComponent\Attribute\LiveAction;
use Symfony\UX\LiveComponent\Attribute\LiveArg;
use Symfony\UX\LiveComponent\Attribute\LiveListener;
use Symfony\UX\LiveComponent\Attribute\LiveProp;
use Symfony\UX\LiveComponent\ComponentToolsTrait;
use Symfony\UX\LiveComponent\DefaultActionTrait;
use Symfony\UX\LiveComponent\Metadata\UrlMapping;

#[AsLiveComponent]
final class DocumentsListing extends AbstractController
{
    public const MODAL_NAME = 'document';

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
        private readonly DocumentRepository $documentRepository,
    ) {
    }

    #[LiveListener('document:update')]
    public function onDocumentUpdate(#[LiveArg] ?int $document = null): void
    {
        $this->paginator = null;
    }

    #[LiveAction]
    public function createDocument(): void
    {
        $this->emit('document:create');
    }

    private function countDocuments(): int
    {
        if (!empty($this->search)) {
            return $this->documentRepository->countByQuery($this->search);
        }

        return $this->documentRepository->countAll();
    }

    /**
     * @return Document[]
     */
    private function fetchDocuments(int $limit, int $offset): array
    {
        if (!empty($this->search)) {
            return $this->documentRepository->search(
                query: $this->search,
                limit: $limit,
                offset: $offset,
            );
        }

        return $this->documentRepository->findPaginated(
            limit: $limit,
            offset: $offset,
        );
    }

    /**
     * @return Paginator<Document>
     */
    protected function getPaginator(): Paginator
    {
        if ($this->paginator === null) {
            $this->paginator = new Paginator(
                currentPage: $this->page,
                itemsPerPage: self::PER_PAGE,
                countCallback: $this->countDocuments(...),
                fetchCallback: $this->fetchDocuments(...),
            );
        }

        return $this->paginator;
    }

    /**
     * @return Document[]
     */
    public function getDocuments(): array
    {
        return $this->getItems();
    }

    public function getTotalDocuments(): int
    {
        return $this->getTotalItems();
    }

    public function getModalName(): string
    {
        return self::MODAL_NAME;
    }
}
