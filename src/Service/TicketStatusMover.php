<?php declare(strict_types=1);

namespace App\Service;

use App\Entity\TicketStatus;
use App\Repository\TicketStatusRepository;
use ValueError;

class TicketStatusMover
{
    public function __construct(
        private readonly TicketStatusRepository $ticketStatusRepository,
    ) {
    }

    public function move(
        int|TicketStatus $status,
        int|TicketStatus|null $precedingStatus = null,
    ): void
    {
        $statuses = $this->ticketStatusRepository->findAllInDisplayOrder();

        $statusId = $this->statusId($status);
        $precedingStatusId = $this->statusId($precedingStatus);

        $moved = null;
        $remaining = [];
        foreach ($statuses as $status) {
            if ($status->getId() === $statusId) {
                $moved = $status;
                continue;
            }
            $remaining[] = $status;
        }

        if (!$moved) throw new ValueError("Status with $statusId not found");

        $insertIndex = 0;

        if ($precedingStatusId !== null) {
            foreach ($remaining as $i => $status) {
                if ($status->getId() === $precedingStatusId) {
                    $insertIndex = $i + 1;
                    break;
                }
            }
        }

        array_splice($remaining, $insertIndex, 0, [$moved]);
        foreach ($remaining as $i => $statusToUpdate) {
            $statusToUpdate->setDisplayOrder($i);
        }
    }

    private function statusId(int|TicketStatus|null $status): ?int
    {
        return $status instanceof TicketStatus ? $status->getId() : $status;
    }
}
