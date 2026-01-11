<?php declare(strict_types=1);

namespace App\Twig\Components\User;

use App\Entity\Project;
use App\Repository\ProjectRepository;
use App\Service\Paginator;
use App\Service\PaginatorTrait;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\UX\LiveComponent\Attribute\AsLiveComponent;
use Symfony\UX\LiveComponent\Attribute\LiveAction;
use Symfony\UX\LiveComponent\Attribute\LiveArg;
use Symfony\UX\LiveComponent\Attribute\LiveListener;
use Symfony\UX\LiveComponent\Attribute\LiveProp;
use Symfony\UX\LiveComponent\ComponentToolsTrait;
use Symfony\UX\LiveComponent\DefaultActionTrait;
use Symfony\UX\LiveComponent\Metadata\UrlMapping;

#[AsLiveComponent]
final class ProjectsListing extends AbstractController
{
    public const MODAL_NAME = 'project';

    use ComponentToolsTrait;
    use DefaultActionTrait;
    use PaginatorTrait;

    #[LiveProp(writable: true, url: new UrlMapping(as: 'q'))]
    public ?string $search = null;

    #[LiveProp(writable: true, url: true)]
    public bool $onlyFinished = false;

    #[LiveProp(writable: true, url: true)]
    public int $page = 1;

    private const PER_PAGE = 20;

    private ?Paginator $paginator = null;

    public function __construct(
        private readonly ProjectRepository $projectRepository,
        private readonly EntityManagerInterface $em,
    ) {
    }

    #[LiveListener('project:update')]
    public function onProjectUpdate(#[LiveArg] ?int $project = null) : void
    {
    }

    #[LiveAction]
    public function editProject(#[LiveArg] ?int $projectId = null): void
    {
        $this->emit('project:edit', ['project' => $projectId]);
    }

    #[LiveAction]
    public function toggleProjectStatus(#[LiveArg] int $projectId): void
    {
        if (!($project = $this->projectRepository->find($projectId))) {
            return;
        }

        $project->setIsFinished(!$project->isFinished());
        $this->em->flush();
    }

    private function countProjects(): int
    {
        return $this->projectRepository->countSearch(
            search: $this->search,
            onlyFinished: $this->onlyFinished,
        );
    }

    /**
     * @return Project[]
     */
    private function fetchProjects(int $limit, int $offset): array
    {
        return $this->projectRepository->search(
            search: $this->search,
            onlyFinished: $this->onlyFinished,
            limit: $limit,
            offset: $offset,
        );
    }

    /**
     * @return Paginator<Project>
     */
    protected function getPaginator(): Paginator
    {
        if ($this->paginator === null) {
            $this->paginator = new Paginator(
                currentPage: $this->page,
                itemsPerPage: self::PER_PAGE,
                countCallback: $this->countProjects(...),
                fetchCallback: $this->fetchProjects(...),
            );
        }

        return $this->paginator;
    }

    /**
     * @return Project[]
     */
    public function getProjects(): array
    {
        return $this->getItems();
    }

    public function getTotalProjects(): int
    {
        return $this->getTotalItems();
    }

    public function getModalName(): string
    {
        return self::MODAL_NAME;
    }
}
