<?php declare(strict_types=1);

namespace App\DTO;

use App\Entity\TicketStatus;

readonly class TicketStatusWithTicketCount
{
    public function __construct(
        public TicketStatus $status,
        public int $tickets,
    ) {
    }
}
