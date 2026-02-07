<?php declare(strict_types=1);

namespace App\Controller\User;

use App\Entity\Contact;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\ExpressionLanguage\Expression;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsCsrfTokenValid;

final class ContactController extends AbstractController
{
    #[Route('/contacts', name: 'app_user_contacts')]
    public function index(): Response
    {
        return $this->render('listings/listing.html.twig', [
            'title' => 'Contact',
            'headerTitle' => 'Contact',
            'listing' => '_contacts.html.twig',
            'entityClassName' => Contact::class,
        ]);
    }

    #[Route('/contacts/{id}', name: 'app_user_contact_view')]
    public function view(Contact $contact): Response
    {
        return $this->render('user/contact_view.html.twig', [
            'contact' => $contact,
        ]);
    }

    #[Route('/contacts/{id}/delete', name: 'app_user_contact_delete', methods: 'POST')]
    #[IsCsrfTokenValid(new Expression('"delete-contact-" ~ args["contact"].getId()'))]
    public function delete(Contact $contact, EntityManagerInterface $em): Response
    {
        $em->remove($contact);
        $em->flush();
        return $this->redirectToRoute('app_user_contacts');
    }
}
