<?php declare(strict_types=1);

namespace App\Twig\Components\User;

use App\Entity\Company;
use App\Form\CompanyType;
use App\Repository\CompanyRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Form\FormInterface;
use Symfony\UX\LiveComponent\Attribute\AsLiveComponent;
use Symfony\UX\LiveComponent\Attribute\LiveAction;
use Symfony\UX\LiveComponent\Attribute\LiveArg;
use Symfony\UX\LiveComponent\Attribute\LiveListener;
use Symfony\UX\LiveComponent\Attribute\LiveProp;
use Symfony\UX\LiveComponent\ComponentToolsTrait;
use Symfony\UX\LiveComponent\ComponentWithFormTrait;
use Symfony\UX\LiveComponent\DefaultActionTrait;

#[AsLiveComponent]
final class CompanyEdit extends AbstractController
{
    use ComponentToolsTrait;
    use ComponentWithFormTrait;
    use DefaultActionTrait;

    #[LiveProp]
    public string $editModalName = 'company';

    #[LiveProp]
    public ?Company $viewCompany = null;

    public function __construct(
        private readonly CompanyRepository $companyRepository,
        private readonly EntityManagerInterface $em,
    ) {
    }

    protected function instantiateForm(): FormInterface
    {
        return $this->createForm(CompanyType::class, $this->viewCompany);
    }

    #[LiveAction]
    public function save(): void
    {
        $this->submitForm();

        /** @var Company */
        $company = $this->getForm()->getData();

        $this->em->persist($company);
        $this->em->flush();

        $this->dispatchBrowserEvent('modal:close', ['id' => $this->editModalName]);
        $this->emit('company:update', ['company' => $company->getId()]);
        $this->viewCompany = null;
        $this->resetForm();
    }

    #[LiveListener('company:edit')]
    public function onCompanyEdit(#[LiveArg] ?int $company = null): void
    {
        $this->viewCompany = !$company
            ? null
            : $this->companyRepository->find($company)
        ;

        if ($company && !$this->viewCompany) {
            throw new \ValueError('Company with id "' . ($company) . '" does not exist');
        }

        $this->dispatchBrowserEvent('modal:open', ['id' => $this->editModalName]);
        $this->resetForm();
    }
}
