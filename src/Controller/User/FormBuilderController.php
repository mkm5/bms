<?php declare(strict_types=1);

namespace App\Controller\User;

use App\Entity\FormDefinition;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

final class FormBuilderController extends AbstractController
{
    #[Route('/forms', name: 'app_user_forms')]
    public function index(): Response
    {
        return $this->render('user/forms.html.twig');
    }

    #[Route('/form-builder', name: 'app_user_form_builder_new')]
    #[Route('/form-builder/{id}', name: 'app_user_form_builder_edit')]
    public function edit(?FormDefinition $formDefinition = null): Response
    {
        return $this->render('user/form_builder_edit.html.twig', [
            'formDefinition' => $formDefinition,
        ]);
    }
}
