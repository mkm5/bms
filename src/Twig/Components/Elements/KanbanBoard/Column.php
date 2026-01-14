<?php declare(strict_types=1);

namespace App\Twig\Components\Elements\KanbanBoard;

use App\Entity\Ticket;
use App\Entity\TicketStatus;
use Symfony\UX\TwigComponent\Attribute\AsTwigComponent;
use Symfony\UX\TwigComponent\Attribute\PostMount;

#[AsTwigComponent]
class Column
{
    public TicketStatus $status;

    /** @var Ticket[] */
    public array $tickets;

    #[PostMount]
    public function postMount(): void
    {
        usort(
            $this->tickets,
            fn(Ticket $a, Ticket $b) => $a->getDisplayOrder() <=> $b->getDisplayOrder(),
        );
    }
}
