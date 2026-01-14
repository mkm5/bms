<?php declare(strict_types=1);

namespace App\Twig\Components\Elements\KanbanBoard;

use App\Entity\Ticket as TicketModel;
use Symfony\UX\TwigComponent\Attribute\AsTwigComponent;

#[AsTwigComponent]
final class Ticket
{
    public TicketModel $ticket;
}
