<?php declare(strict_types=1);

namespace App\Controller\User;

use App\Entity\FormDefinition;
use App\Entity\FormSubmission;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

final class FormBuilderController extends AbstractController
{

    #[Route('/form-builder', name: 'app_user_form_builder_new')]
    #[Route('/form-builder/{id}', name: 'app_user_form_builder_edit')]
    public function edit(?FormDefinition $formDefinition = null): Response
    {
        return $this->render('user/form_builder_edit.html.twig', [
            'formDefinition' => $formDefinition,
        ]);
    }

    #[Route('/forms', name: 'app_user_forms')]
    public function index(): Response
    {
        return $this->render('listings/listing.html.twig', [
            'title' => 'Form',
            'headerTitle' => 'Form',
            'listing' => '_forms.html.twig',
            'entityClassName' => FormDefinition::class,
        ]);
    }

    #[Route('/forms/{id}/submissions', name: 'app_user_form_submissions')]
    public function submissions(FormDefinition $formDefinition): Response
    {
        $title = $formDefinition->getName() . ' - Submissions';
        return $this->render('listings/listing.html.twig', [
            'title' => $title,
            'headerTitle' => $title,
            'listing' => '_form_submissions.html.twig',
            'entityClassName' => FormSubmission::class,
            'formSubmissionsListingProps' => [
                'params' => [
                    'form' => $formDefinition->getId(),
                ]
            ],
            'form' => $formDefinition,
        ]);
    }
}
