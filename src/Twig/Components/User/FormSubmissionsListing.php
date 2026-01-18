<?php declare(strict_types=1);

namespace App\Twig\Components\User;

use App\Entity\FormDefinition;
use App\Entity\FormSubmission;
use App\Repository\FormSubmissionRepository;
use App\Service\Paginator;
use App\Service\PaginatorTrait;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\UX\LiveComponent\Attribute\AsLiveComponent;
use Symfony\UX\LiveComponent\Attribute\LiveAction;
use Symfony\UX\LiveComponent\Attribute\LiveArg;
use Symfony\UX\LiveComponent\Attribute\LiveProp;
use Symfony\UX\LiveComponent\DefaultActionTrait;
use Symfony\UX\LiveComponent\Metadata\UrlMapping;

#[AsLiveComponent]
final class FormSubmissionsListing extends AbstractController
{
    use DefaultActionTrait;
    use PaginatorTrait;

    #[LiveProp(writable: true, url: new UrlMapping(as: 'q'))]
    public ?string $search = null;

    #[LiveProp(writable: true, url: true)]
    public int $page = 1;

    #[LiveProp]
    public FormDefinition $formDefinition;

    private const PER_PAGE = 20;

    private ?Paginator $paginator = null;

    public function __construct(
        private readonly FormSubmissionRepository $formSubmissionRepository,
    ) {
    }

    private function countSubmissions(): int
    {
        if ($this->search !== null && $this->search !== '') {
            return $this->formSubmissionRepository->countByQuery($this->formDefinition, $this->search);
        }

        return $this->formSubmissionRepository->countByForm($this->formDefinition);
    }

    /**
     * @return FormSubmission[]
     */
    private function fetchSubmissions(int $limit, int $offset): array
    {
        if ($this->search !== null && $this->search !== '') {
            return $this->formSubmissionRepository->search(
                form: $this->formDefinition,
                query: $this->search,
                limit: $limit,
                offset: $offset,
            );
        }

        return $this->formSubmissionRepository->findByForm(
            form: $this->formDefinition,
            limit: $limit,
            offset: $offset,
        );
    }

    /**
     * @return Paginator<FormSubmission>
     */
    protected function getPaginator(): Paginator
    {
        if ($this->paginator === null) {
            $this->paginator = new Paginator(
                currentPage: $this->page,
                itemsPerPage: self::PER_PAGE,
                countCallback: $this->countSubmissions(...),
                fetchCallback: $this->fetchSubmissions(...),
            );
        }

        return $this->paginator;
    }

    /**
     * @return FormSubmission[]
     */
    public function getSubmissions(): array
    {
        return $this->getItems();
    }

    public function getTotalSubmissions(): int
    {
        return $this->getTotalItems();
    }

    public function getFields(): array
    {
        return $this->formDefinition->getFields()->toArray();
    }

    #[LiveAction]
    public function delete(#[LiveArg] int $id): void
    {
        $submission = $this->formSubmissionRepository->find($id);
        if ($submission && $submission->getForm()?->getId() === $this->formDefinition->getId()) {
            $this->formSubmissionRepository->delete($submission);
        }
        $this->paginator = null;
    }
}
