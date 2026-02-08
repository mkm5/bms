<?php declare(strict_types=1);

namespace App\Twig\Components\User;

use App\Entity\Project;
use App\Event\ProjectFinishedEvent;
use App\Repository\DocumentRepository;
use App\Repository\ProjectRepository;
use App\Security\Voter\ProjectVoter;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Contracts\EventDispatcher\EventDispatcherInterface;
use Symfony\UX\LiveComponent\Attribute\AsLiveComponent;
use Symfony\UX\LiveComponent\Attribute\LiveAction;
use Symfony\UX\LiveComponent\Attribute\LiveArg;
use Symfony\UX\LiveComponent\Attribute\LiveListener;
use Symfony\UX\LiveComponent\Attribute\LiveProp;
use Symfony\UX\LiveComponent\ComponentToolsTrait;
use Symfony\UX\LiveComponent\DefaultActionTrait;

#[AsLiveComponent]
final class ProjectView extends AbstractController
{
    use ComponentToolsTrait;
    use DefaultActionTrait;

    #[LiveProp]
    public Project $project;

    #[LiveProp]
    public array $formListingConfig;

    #[LiveProp]
    public array $ticketListingConfig;

    public function __construct(
        private readonly ProjectRepository $projectRepository,
        private readonly DocumentRepository $documentRepository,
        private readonly EntityManagerInterface $em,
        private readonly EventDispatcherInterface $eventDispatcher,
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
    public function toggleProjectStatus(): RedirectResponse
    {
        $this->denyAccessUnlessGranted(ProjectVoter::FINISH, $this->project);
        $this->project->setIsFinished(!$this->project->isFinished());
        if ($this->project->isFinished()) {
            $this->eventDispatcher->dispatch(new ProjectFinishedEvent($this->project));
        }

        $this->em->flush();

        return $this->redirectToRoute('app_user_project_view', ['id' => $this->project->getId()]);
    }

    #[LiveAction]
    public function removeDocument(#[LiveArg] int $documentId): void
    {
        if ($this->project->isFinished()) {
            throw $this->createAccessDeniedException('Cannot remove documents from a finished project.');
        }

        $document = $this->documentRepository->find($documentId);
        if ($document) {
            $this->project->removeDocument($document);
            $this->em->flush();
        }
    }
}
