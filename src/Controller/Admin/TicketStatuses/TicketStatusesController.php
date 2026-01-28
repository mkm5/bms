<?php declare(strict_types=1);

namespace App\Controller\Admin\TicketStatuses;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

final class TicketStatusesController extends AbstractController
{
    #[Route('/admin/ticket-statuses', name: 'app_admin_ticket_statuses')]
    public function index(): Response
    {
        return $this->render('admin/ticket_statuses.html.twig');
    }
}
