<?php declare(strict_types=1);

namespace App\Repository;

use App\Entity\Ticket;
use App\Entity\TicketStatus;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Ticket>
 */
class TicketRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Ticket::class);
    }

    /**
     * @return Ticket[]
     */
    public function findByStatusInDisplayOrder(TicketStatus $status): array
    {
        return $this->createQueryBuilder('t')
            ->where('t.status = :status')
            ->setParameter('status', $status)
            ->orderBy('t.displayOrder', 'ASC')
            ->getQuery()
            ->getResult()
        ;
    }

    /**
     * @return Ticket[]
     */
    public function findAllForKanban(): array
    {
        return $this->createQueryBuilder('t')
            ->leftJoin('t.project', 'p')
            ->addSelect('p')
            ->leftJoin('t.tags', 'tag')
            ->addSelect('tag')
            ->leftJoin('t.status', 's')
            ->addSelect('s')
            ->addOrderBy('t.displayOrder', 'ASC')
            ->getQuery()
            ->getResult()
        ;
    }

    public function countByStatus(): array
    {
        $result = $this->createQueryBuilder('t')
            ->select('IDENTITY(t.status) as ticket_status', 'COUNT(t.id) as count')
            ->groupBy('t.status')
            ->getQuery()
            ->getArrayResult()
        ;
        return array_column($result, 'count', 'ticket_status');
    }
}
