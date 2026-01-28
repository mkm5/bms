<?php declare(strict_types=1);

namespace App\Repository;

use App\DTO\TicketStatusWithTicketCount;
use App\Entity\TicketStatus;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<TicketStatus>
 */
class TicketStatusRepository extends ServiceEntityRepository
{
    public function __construct(
        ManagerRegistry $registry,
        private readonly TicketRepository $ticketRepository,
    ) {
        parent::__construct($registry, TicketStatus::class);
    }

    /**
     * @return TicketStatus[]
     */
    public function findAllInDisplayOrder(): array
    {
        return $this->createQueryBuilder('ts')
            ->orderBy('ts.displayOrder', 'ASC')
            ->getQuery()
            ->getResult()
        ;
    }

    public function maxDisplayOrder(): int
    {
        return $this->createQueryBuilder('ts')
            ->select('MAX(ts.displayOrder)')
            ->getQuery()
            ->getSingleScalarResult()
        ;
    }

    public function findAllInDisplayOrderWithNumberOfTickets(): array
    {
        $statuses = $this->findAllInDisplayOrder();
        $countByStatuses = $this->ticketRepository->countByStatus();

        return array_map(function(TicketStatus $status) use ($countByStatuses) {
            return new TicketStatusWithTicketCount(
                $status,
                $countByStatuses[$status->getId()] ?? 0
            );
        }, $statuses);
    }
}
