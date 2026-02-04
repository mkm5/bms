<?php declare(strict_types=1);

namespace App\DTO;

use App\Entity\Ticket;

readonly class TicketWithTaskCount
{
    public function __construct(
        public Ticket $ticket,
        public int $tasks,
        public int $completedTasks,
    ) {
    }

    public function getId(): int
    {
        return $this->ticket->getId();
    }
}
