<?php declare(strict_types=1);

namespace App\Controller\User;

use App\Entity\Ticket;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\ExpressionLanguage\Expression;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsCsrfTokenValid;

final class TicketController extends AbstractController
{
    #[Route('/tickets', name: 'app_user_tickets')]
    public function index(): Response
    {
        return $this->render('listings/listing.html.twig', [
            'title' => 'Ticket',
            'headerTitle' => 'Ticket',
            'listing' => '_tickets.html.twig',
            'entityClassName' => Ticket::class,
        ]);
    }

    #[Route('/tickets/{id}/delete', name: 'app_user_ticket_delete', methods: 'POST')]
    #[IsCsrfTokenValid(new Expression('"delete-ticket-" ~ args["ticket"].getId()'))]
    public function delete(Ticket $ticket, EntityManagerInterface $em): Response
    {
        $em->remove($ticket);
        $em->flush();
        return $this->redirectToRoute('app_user_tickets');
    }
}
