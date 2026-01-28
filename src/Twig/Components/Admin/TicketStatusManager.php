<?php declare(strict_types=1);

namespace App\Twig\Components\Admin;

use App\DTO\TicketStatusWithTicketCount;
use App\Entity\TicketStatus;
use App\Repository\TicketStatusRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\UX\LiveComponent\Attribute\AsLiveComponent;
use Symfony\UX\LiveComponent\Attribute\LiveAction;
use Symfony\UX\LiveComponent\Attribute\LiveArg;
use Symfony\UX\LiveComponent\Attribute\LiveProp;
use Symfony\UX\LiveComponent\DefaultActionTrait;

#[AsLiveComponent]
final class TicketStatusManager
{
    use DefaultActionTrait;

    #[LiveProp(writable: true)]
    public string $newStatusName = '';

    public function __construct(
        private readonly TicketStatusRepository $ticketStatusRepository,
        private readonly EntityManagerInterface $em,
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

        $maxOrder = $this->ticketStatusRepository
            ->createQueryBuilder('ts')
            ->select('MAX(ts.displayOrder)')
            ->getQuery()
            ->getSingleScalarResult();

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
        $statuses = $this->ticketStatusRepository->findAllInDisplayOrder();

        $moved = null;
        $remaining = [];
        foreach ($statuses as $status) {

            if ($status->getId() === $statusId) {
                $moved = $status;
                continue;
            }
            $remaining[] = $status;
        }

        if (!$moved) return;

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
        foreach ($remaining as $i => $status) {
            $status->setDisplayOrder($i);
        }

        $this->em->flush();
    }
}
