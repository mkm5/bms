<?php declare(strict_types=1);

namespace App\Controller\User;

use App\Entity\Document;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\ExpressionLanguage\Expression;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsCsrfTokenValid;

final class DocumentController extends AbstractController
{
    #[Route('/documents', name: 'app_user_documents')]
    public function index(): Response
    {
        return $this->render('listings/listing.html.twig', [
            'title' => 'Document',
            'headerTitle' => 'Document',
            'listing' => '_documents.html.twig',
            'entityClassName' => Document::class,
        ]);
    }

    #[Route('/documents/{id}', name: 'app_user_document_view')]
    public function view(Document $document): Response
    {
        return $this->render('user/document_view.html.twig', ['document' => $document]);
    }

    #[Route('/documents/{id}/delete', name: 'app_user_document_delete', methods: 'POST')]
    #[IsCsrfTokenValid(new Expression('"delete-document-" ~ args["document"].getId()'))]
    public function delete(Document $document, EntityManagerInterface $em): Response
    {
        $em->remove($document);
        $em->flush();
        return $this->redirectToRoute('app_user_documents');
    }
}
