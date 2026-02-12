<?php declare(strict_types=1);

namespace App\Service;

use App\Entity\Ticket;
use App\Entity\TicketStatus;
use App\Repository\TicketRepository;
use App\Repository\TicketStatusRepository;
use ValueError;

class TicketMover
{
    public function __construct(
        private readonly TicketStatusRepository $ticketStatusRepository,
        private readonly TicketRepository $ticketRepository,
    ) {
    }

    public function move(
        int|Ticket $ticket,
        int|TicketStatus $status,
        int|Ticket|null $precedingTicket,
    ): void
    {
        if (!$ticket instanceof Ticket) {
            /** @var Ticket */
            if (!($ticket = $this->ticketRepository->find($ticket))) {
                throw new ValueError('Ticket not found');
            }
        }

        if (!$status instanceof TicketStatus) {
            /** @var TicketStatus */
            if (!($status = $this->ticketStatusRepository->find($status))) {
                throw new ValueError('Ticket status not found');
            }
        }

        if ($precedingTicket instanceof Ticket) {
            /** @var int|null */
            $precedingTicket = $precedingTicket->getId();
        }

        $ticket->setDisplayOrder(0);
        $ticket->setStatus($status);
        $ticketsInStatus = $this->ticketRepository->findByStatusInDisplayOrder($status);

        $i = 0;
        $found = $precedingTicket === null ? true : false;
        foreach ($ticketsInStatus as $ticketInStatus) {
            if ($precedingTicket !== null && $ticketInStatus->getId() === $precedingTicket) {
                $ticket->setDisplayOrder($ticketInStatus->getDisplayOrder() + 1);
                $found = true;
                continue;
            }

            if (!$found || $ticketInStatus->getId() === $ticket->getId()) {
                continue;
            }

            $ticketInStatus->setDisplayOrder($ticket->getDisplayOrder() + $i++ + 1);
        }
    }
}
