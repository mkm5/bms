<?php declare(strict_types=1);

namespace App\Controller\User;

use App\Entity\FormDefinition;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/form-builder')]
final class FormBuilderController extends AbstractController
{
    #[Route('/', name: 'app_user_form_builder_new')]
    #[Route('/{id}', name: 'app_user_form_builder_edit')]
    public function edit(?FormDefinition $formDefinition = null): Response
    {
        return $this->render('user/form_builder_edit.html.twig', [
            'formDefinition' => $formDefinition,
        ]);
    }
}
