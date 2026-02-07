<?php declare(strict_types=1);

namespace App\Controller\User;

use App\Entity\FormDefinition;
use App\Entity\Project;
use App\Entity\Ticket;
use App\Security\Voter\ProjectVoter;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\ExpressionLanguage\Expression;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsCsrfTokenValid;
use Symfony\Component\Security\Http\Attribute\IsGranted;

final class ProjectController extends AbstractController
{
    #[Route('/projects', name: 'app_user_projects')]
    public function index(): Response
    {
        return $this->render('listings/listing.html.twig', [
            'title' => 'Project',
            'headerTitle' => 'Project',
            'listing' => '_projects.html.twig',
            'entityClassName' => Project::class,
        ]);
    }

    #[Route('/projects/{id}', name: 'app_user_project_view')]
    public function view(Project $project): Response
    {
        return $this->render('user/project_view.html.twig', [
            'project' => $project,
            'formListingConfig' => [
                'entityClassName' => FormDefinition::class,
                'formsListingProps' => [
                    'exposeSearch' => false,
                    'params' => ['project' => $project->getId()],
                ],
            ],
            'ticketListingConfig' => [
                'entityClassName' => Ticket::class,
                'ticketsListingProps' => [
                    'exposeSearch' => false,
                    'params' => ['project' => $project->getId()],
                ],
            ],
        ]);
    }

    #[Route('/projects/{id}/delete', name: 'app_user_project_delete', methods: 'POST')]
    #[IsGranted(ProjectVoter::DELETE, 'project')]
    #[IsCsrfTokenValid(new Expression('"delete-project-" ~ args["project"].getId()'))]
    public function delete(Project $project, EntityManagerInterface $em): Response
    {
        $em->remove($project);
        $em->flush();
        return $this->redirectToRoute('app_user_projects');
    }
}
