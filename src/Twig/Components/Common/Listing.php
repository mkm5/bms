<?php declare(strict_types=1);

namespace App\Twig\Components\Common;

use App\Repository\SearchableRepository;
use App\Service\Paginator;
use App\Service\PaginatorTrait;
use App\Service\SearchableRepositoryProvider;
use Symfony\UX\LiveComponent\Attribute\AsLiveComponent;
use Symfony\UX\LiveComponent\Attribute\LiveListener;
use Symfony\UX\LiveComponent\Attribute\LiveProp;
use Symfony\UX\LiveComponent\Attribute\PreReRender;
use Symfony\UX\LiveComponent\DefaultActionTrait;
use Symfony\UX\LiveComponent\Metadata\UrlMapping;

#[AsLiveComponent]
class Listing
{
    use DefaultActionTrait;
    use PaginatorTrait;

    /** @var class-string */
    #[LiveProp]
    public string $entityClassName;

    #[LiveProp]
    public bool $exposeSearch = true;

    #[LiveProp]
    public ?string $searchPrefix = null;

    #[LiveProp(writable: true, url: new UrlMapping(as: 'query'), modifier: 'modifyProp')]
    public ?string $query = null;

    /** @var array<string, mixed> */
    #[LiveProp(writable: true, url: new UrlMapping(as: 'params'), modifier: 'modifyProp')]
    public array $params = [];

    #[LiveProp(writable: true, url: new UrlMapping(as: 'page'), modifier: 'modifyProp')]
    public int $page = 1;

    #[LiveProp]
    public int $maxItems = 10;

    private ?SearchableRepository $repository;

    private ?Paginator $paginator;

    public function __construct(
        private readonly SearchableRepositoryProvider $repositoryProvider,
    ) {
    }

    #[PreReRender]
    public function doMount()
    {
        // Note: `mount` is not executed during Action
        $this->mount($this->entityClassName, $this->params);
    }

    public function mount(string $entityClassName, array $params = []): void
    {
        $this->entityClassName = $entityClassName;
        $this->repository = $this->repositoryProvider->getRepository($entityClassName);
        $this->params = $params;
    }

    public function modifyProp(LiveProp $liveProp): LiveProp
    {
        if (!$this->exposeSearch) {
            return $liveProp->withUrl(false);
        }
        $alias = (!empty($this->searchPrefix) ? $this->searchPrefix.'.' : '').$liveProp->url()->as;
        return $liveProp->withUrl(new UrlMapping(as: $alias));
    }

    public function hasParam(string $param): bool
    {
        return isset($this->params[$param]);
    }

    public function getParam(string $param, $default = null)
    {
        return $this->hasParam($param) ? $this->params[$param] : $default;
    }

    #[LiveListener('listing:refresh')]
    public function onRefresh(): void
    {
        $this->paginator = null;
    }

    private function countItems(): int
    {
        return $this->repository->searchCount($this->query, $this->params);
    }

    private function fetchItems(int $limit, int $offset): array
    {
        return $this->repository->search($this->query, $this->params, $limit, $offset);
    }

    protected function getPaginator(): Paginator
    {
        return $this->paginator ??= new Paginator(
            currentPage: $this->page,
            itemsPerPage: $this->maxItems,
            countCallback: $this->countItems(...),
            fetchCallback: $this->fetchItems(...),
        );
    }
}
