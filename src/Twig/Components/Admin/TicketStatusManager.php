<?php declare(strict_types=1);

namespace App\Twig\Components\Admin;

use App\DTO\TicketStatusWithTicketCount;
use App\Entity\TicketStatus;
use App\Repository\TicketStatusRepository;
use App\Service\TicketStatusMover;
use Doctrine\ORM\EntityManagerInterface;
use Psr\Log\LoggerInterface;
use Symfony\UX\LiveComponent\Attribute\AsLiveComponent;
use Symfony\UX\LiveComponent\Attribute\LiveAction;
use Symfony\UX\LiveComponent\Attribute\LiveArg;
use Symfony\UX\LiveComponent\Attribute\LiveProp;
use Symfony\UX\LiveComponent\DefaultActionTrait;
use ValueError;

#[AsLiveComponent]
final class TicketStatusManager
{
    use DefaultActionTrait;

    #[LiveProp(writable: true)]
    public string $newStatusName = '';

    public function __construct(
        private readonly TicketStatusRepository $ticketStatusRepository,
        private readonly EntityManagerInterface $em,
        private readonly TicketStatusMover $ticketStatusMover,
        private readonly LoggerInterface $logger,
    ) {
    }

    /** @return TicketStatusWithTicketCount[] */
    public function getStatusesWithCount(): array
    {
        return $this->ticketStatusRepository->findAllInDisplayOrderWithNumberOfTickets();
    }

    #[LiveAction]
    public function addStatus(): void
    {
        $name = trim($this->newStatusName);
        if ($name === '') {
            return;
        }

        $maxOrder = $this->ticketStatusRepository->maxDisplayOrder();

        $status = TicketStatus::create($name, ((int) $maxOrder) + 1);
        $this->em->persist($status);
        $this->em->flush();

        $this->newStatusName = '';
    }

    #[LiveAction]
    public function removeStatus(#[LiveArg] int $statusId): void
    {
        $status = $this->ticketStatusRepository->find($statusId);
        if (!$status || $status->getTickets()->count() > 0) {
            return;
        }

        $this->em->remove($status);
        $this->em->flush();
    }

    #[LiveAction]
    public function reorder(#[LiveArg] int $statusId, #[LiveArg] ?int $precedingStatusId): void
    {
        try {
            $this->ticketStatusMover->move($statusId, $precedingStatusId);
            $this->em->flush();
        } catch (ValueError $e) {
            $this->logger->warning('Error while moving ticket status', [
                'status' => $statusId,
                'precedingStatus' => $precedingStatusId,
                'error' => $e->getMessage(),
            ]);
        }
    }
}
