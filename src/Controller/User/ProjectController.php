<?php declare(strict_types=1);

namespace App\Controller\User;

use App\Entity\FormDefinition;
use App\Entity\Project;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;;
use Symfony\Component\Routing\Attribute\Route;

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
                    'params' => [
                        'project' => $project->getId(),
                    ],
                ],
            ],
        ]);
    }
}
