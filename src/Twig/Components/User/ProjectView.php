<?php declare(strict_types=1);

namespace App\Twig\Components\User;

use App\Entity\Project;
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

    public const MODAL_NAME = 'project';

    #[LiveProp]
    public Project $project;

    public function __construct(
        private readonly ProjectRepository $projectRepository,
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
    public function editProject(): void
    {
        $this->emit('project:edit', ['project' => $this->project->getId()]);
    }

    #[LiveAction]
    public function toggleProjectStatus(): void
    {
        $this->project->setIsFinished(!$this->project->isFinished());
        $this->em->flush();
    }

    public function getModalName(): string
    {
        return self::MODAL_NAME;
    }
}
