<?php declare(strict_types=1);

namespace App\Repository;

use App\Entity\TicketStatus;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<TicketStatus>
 */
class TicketStatusRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
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
}
