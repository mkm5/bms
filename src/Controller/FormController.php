<?php declare(strict_types=1);

namespace App\Controller;

use App\Entity\FormDefinition;
use App\Entity\FormSubmission;
use App\Service\FormDefinitionFormBuilder;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

final class FormController extends AbstractController
{
    #[Route('/f/{id}', name: 'app_form_show')]
    public function show(
        FormDefinition $formDefinition,
        Request $request,
        FormDefinitionFormBuilder $formBuilder,
        EntityManagerInterface $em,
    ): Response
    {
        $form = $formBuilder->createForm($formDefinition);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $data = $form->getData();

            $formSubmission = FormSubmission::create($formDefinition, $data);
            $em->persist($formSubmission);
            $em->flush();

            $this->addFlash('success', 'Form submitted successfully!');

            return $this->redirectToRoute('app_form_success', ['id' => $formDefinition->getId()]);
        }

        return $this->render('Public/form/show.html.twig', [
            'formDefinition' => $formDefinition,
            'form' => $form,
        ]);
    }

    #[Route('/f/{id}/success', name: 'app_form_success')]
    public function success(FormDefinition $formDefinition): Response
    {
        return $this->render('Public/form/success.html.twig', ['formDefinition' => $formDefinition]);
    }
}
