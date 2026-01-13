<?php declare(strict_types=1);

namespace App\Twig\Components\User;

use App\Entity\Company;
use App\Repository\CompanyRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\UX\LiveComponent\Attribute\AsLiveComponent;
use Symfony\UX\LiveComponent\Attribute\LiveAction;
use Symfony\UX\LiveComponent\Attribute\LiveArg;
use Symfony\UX\LiveComponent\Attribute\LiveListener;
use Symfony\UX\LiveComponent\Attribute\LiveProp;
use Symfony\UX\LiveComponent\ComponentToolsTrait;
use Symfony\UX\LiveComponent\DefaultActionTrait;

#[AsLiveComponent]
final class CompanyView
{
    use ComponentToolsTrait;
    use DefaultActionTrait;

    public const MODAL_NAME = 'company';

    #[LiveProp]
    public Company $company;

    public function __construct(
        private readonly CompanyRepository $companyRepository,
        private readonly EntityManagerInterface $em,
    ) {
    }

    #[LiveListener('company:update')]
    public function onCompanyUpdate(#[LiveArg] ?int $company = null): void
    {
        if ($company && $this->company->getId() === $company) {
            $this->company = $this->companyRepository->find($company);
        }
    }

    #[LiveAction]
    public function editCompany(): void
    {
        $this->emit('company:edit', ['company' => $this->company->getId()]);
    }

    public function getModalName(): string
    {
        return self::MODAL_NAME;
    }
}
