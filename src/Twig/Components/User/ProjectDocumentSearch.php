<?php declare(strict_types=1);

namespace App\Twig\Components\User;

use App\Entity\Document;
use App\Entity\Project;
use App\Repository\DocumentRepository;
use App\Repository\ProjectRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\UX\LiveComponent\Attribute\AsLiveComponent;
use Symfony\UX\LiveComponent\Attribute\LiveAction;
use Symfony\UX\LiveComponent\Attribute\LiveArg;
use Symfony\UX\LiveComponent\Attribute\LiveListener;
use Symfony\UX\LiveComponent\Attribute\LiveProp;
use Symfony\UX\LiveComponent\ComponentToolsTrait;
use Symfony\UX\LiveComponent\DefaultActionTrait;

#[AsLiveComponent]
final class ProjectDocumentSearch
{
    use ComponentToolsTrait;
    use DefaultActionTrait;

    #[LiveProp]
    public string $modalName;

    #[LiveProp(writable: true)]
    public string $search = '';

    #[LiveProp]
    public ?Project $project = null;

    public function __construct(
        private readonly DocumentRepository $documentRepository,
        private readonly ProjectRepository $projectRepository,
        private readonly EntityManagerInterface $em,
    ) {
    }

    #[LiveListener('project:assign-document')]
    public function onOpenModal(#[LiveArg] int $project): void
    {
        $this->project = $this->projectRepository->find($project);
        $this->search = '';
        $this->dispatchBrowserEvent('modal:open', ['id' => $this->modalName]);
    }

    #[LiveAction]
    public function addDocument(#[LiveArg] int $documentId): void
    {
        if (!$this->project) {
            return;
        }

        $document = $this->documentRepository->find($documentId);
        if (!$document) {
            return;
        }

        $this->project->addDocument($document);
        $this->em->flush();

        $this->dispatchBrowserEvent('modal:close', ['id' => $this->modalName]);
        $this->emit('project:update', ['project' => $this->project->getId()]);
    }

    /**
     * @return Document[]
     */
    public function getDocuments(): array
    {
        return $this->documentRepository->search(query: trim($this->search), limit: 10, offset: 0);
    }
}
