<?php declare(strict_types=1);

namespace App\Controller\User;

use App\Entity\Document;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

final class DocumentController extends AbstractController
{
    #[Route('/documents', name: 'app_user_documents')]
    public function index(): Response
    {
        return $this->render('user/documents.html.twig');
    }

    #[Route('/documents/{id}', name: 'app_user_document_view')]
    public function view(Document $document): Response
    {
        return $this->render('user/document_view.html.twig', ['document' => $document]);
    }
}
