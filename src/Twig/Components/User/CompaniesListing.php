<?php declare(strict_types=1);

namespace App\Twig\Components\User;

use App\Entity\Company;
use App\Repository\CompanyRepository;
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
final class CompaniesListing extends AbstractController
{
    public const MODAL_NAME = 'company';

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
        private readonly CompanyRepository $companyRepository,
        private readonly EntityManagerInterface $em,
    ) {
    }

    #[LiveListener('company:update')]
    public function onCompanyUpdate(#[LiveArg] ?int $company = null): void
    {
    }

    #[LiveAction]
    public function editCompany(#[LiveArg] ?int $companyId = null): void
    {
        $this->emit('company:edit', ['company' => $companyId]);
    }

    private function countCompanies(): int
    {
        return $this->companyRepository->countSearch(
            search: $this->search,
        );
    }

    /**
     * @return Company[]
     */
    private function fetchCompanies(int $limit, int $offset): array
    {
        return $this->companyRepository->search(
            search: $this->search,
            limit: $limit,
            offset: $offset,
        );
    }

    /**
     * @return Paginator<Company>
     */
    protected function getPaginator(): Paginator
    {
        if ($this->paginator === null) {
            $this->paginator = new Paginator(
                currentPage: $this->page,
                itemsPerPage: self::PER_PAGE,
                countCallback: $this->countCompanies(...),
                fetchCallback: $this->fetchCompanies(...),
            );
        }

        return $this->paginator;
    }

    /**
     * @return Company[]
     */
    public function getCompanies(): array
    {
        return $this->getItems();
    }

    public function getTotalCompanies(): int
    {
        return $this->getTotalItems();
    }

    public function getModalName(): string
    {
        return self::MODAL_NAME;
    }
}
