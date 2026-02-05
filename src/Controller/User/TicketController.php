<?php declare(strict_types=1);

namespace App\Controller\User;

use App\Entity\Ticket;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

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
}
