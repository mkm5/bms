<?php declare(strict_types=1);

namespace App\Twig\Components\User;

use App\Entity\FormDefinition;
use App\Entity\Project;
use App\Repository\FormDefinitionRepository;
use App\Repository\FormFieldRepository;
use App\Repository\FormSubmissionRepository;
use App\Service\Paginator;
use App\Service\PaginatorTrait;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\UX\LiveComponent\Attribute\AsLiveComponent;
use Symfony\UX\LiveComponent\Attribute\LiveProp;
use Symfony\UX\LiveComponent\DefaultActionTrait;
use Symfony\UX\LiveComponent\Metadata\UrlMapping;

#[AsLiveComponent]
final class FormsListing extends AbstractController
{
    use DefaultActionTrait;
    use PaginatorTrait;

    #[LiveProp(writable: true, url: new UrlMapping(as: 'q'))]
    public ?string $search = null;

    #[LiveProp(writable: true, url: true)]
    public int $page = 1;

    #[LiveProp]
    public ?Project $project = null;

    private const PER_PAGE = 20;

    private ?Paginator $paginator = null;

    /** @var array<int, int>|null */
    private ?array $fieldCounts = null;

    /** @var array<int, int>|null */
    private ?array $submissionCounts = null;

    public function __construct(
        private readonly FormDefinitionRepository $formDefinitionRepository,
        private readonly FormFieldRepository $formFieldRepository,
        private readonly FormSubmissionRepository $formSubmissionRepository,
    ) {
    }

    public function getFieldCount(FormDefinition $form): int
    {
        if (empty($this->fieldCounts)) {
            $this->fieldCounts = $this->formFieldRepository->countByForms(
                array_map(fn (FormDefinition $f) => $f->getId(), $this->getForms())
            );
        }

        return $this->fieldCounts[$form->getId()] ?? 0;
    }

    public function getSubmissionCount(FormDefinition $form): int
    {
        if (empty($this->submissionCounts)) {
            $this->submissionCounts = $this->formSubmissionRepository->countByForms(
                array_map(fn (FormDefinition $f) => $f->getId(), $this->getForms())
            );
        }

        return $this->submissionCounts[$form->getId()] ?? 0;
    }

    private function countForms(): int
    {
        return $this->formDefinitionRepository->countSearch(
            search: $this->search,
            project: $this->project,
        );
    }

    /**
     * @return FormDefinition[]
     */
    private function fetchForms(int $limit, int $offset): array
    {
        return $this->formDefinitionRepository->search(
            search: $this->search,
            project: $this->project,
            limit: $limit,
            offset: $offset,
        );
    }

    /**
     * @return Paginator<FormDefinition>
     */
    protected function getPaginator(): Paginator
    {
        if ($this->paginator === null) {
            $this->paginator = new Paginator(
                currentPage: $this->page,
                itemsPerPage: self::PER_PAGE,
                countCallback: $this->countForms(...),
                fetchCallback: $this->fetchForms(...),
            );
        }

        return $this->paginator;
    }

    /**
     * @return FormDefinition[]
     */
    public function getForms(): array
    {
        return $this->getItems();
    }

    public function getTotalForms(): int
    {
        return $this->getTotalItems();
    }
}
