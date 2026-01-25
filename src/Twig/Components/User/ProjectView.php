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
final class ProjectView
{
    use ComponentToolsTrait;
    use DefaultActionTrait;

    #[LiveProp]
    public Project $project;

    #[LiveProp]
    public array $formListingConfig;

    public function __construct(
        private readonly ProjectRepository $projectRepository,
        private readonly DocumentRepository $documentRepository,
        private readonly EntityManagerInterface $em,
    ) {
    }

    #[LiveListener('project:update')]
    public function onProjectUpdate(#[LiveArg] ?int $project = null): void
    {
        if ($project && $this->project->getId() === $project) {
            $this->project = $this->projectRepository->find($project);
        }
    }

    #[LiveAction]
    public function toggleProjectStatus(): void
    {
        $this->project->setIsFinished(!$this->project->isFinished());
        $this->em->flush();
    }

    #[LiveAction]
    public function removeDocument(#[LiveArg] int $documentId): void
    {
        $document = $this->documentRepository->find($documentId);
        if ($document) {
            $this->project->removeDocument($document);
            $this->em->flush();
        }
    }
}
