<?php declare(strict_types=1);

namespace App\Twig\Components\User;

use App\Entity\Project;
use App\Form\ProjectType;
use App\Repository\ProjectRepository;
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
final class ProjectEdit extends AbstractController
{
    use ComponentToolsTrait;
    use ComponentWithFormTrait;
    use DefaultActionTrait;

    #[LiveProp]
    public string $modalName;

    #[LiveProp]
    public ?Project $viewProject = null;

    public function __construct(
        private readonly ProjectRepository $projectRepository,
        private readonly EntityManagerInterface $em,
    ) {
    }

    protected function instantiateForm(): FormInterface
    {
        return $this->createForm(ProjectType::class, $this->viewProject);
    }

    #[LiveAction]
    public function save(): void
    {
        $this->submitForm();

        /** @var Project */
        $project = $this->getForm()->getData();

        $this->em->persist($project);
        $this->em->flush();

        $this->dispatchBrowserEvent('modal:close', ['id' => $this->modalName]);
        $this->emit('project:update', ['project' => $project->getId()]);
        $this->viewProject = null;
        $this->resetForm();
    }

    #[LiveListener('project:edit')]
    public function onProjectEdit(#[LiveArg] ?int $project = null): void
    {
        $this->viewProject = !$project
            ? null
            : $this->projectRepository->find($project)
        ;

        if ($project && !$this->viewProject) {
            throw new \ValueError('Project with id "'.($project).'" does not exist');
        }

        $this->dispatchBrowserEvent('modal:open', ['id' => $this->modalName]);
        $this->resetForm();
    }
}
