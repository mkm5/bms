<?php declare(strict_types=1);

namespace App\DTO;

readonly class TicketTaskCount
{
    public function __construct(
        public int $ticket,
        public int $total,
        public int $completed,
    ) {
    }
}
