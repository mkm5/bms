<?php declare(strict_types=1);

namespace App\Repository;

use App\Entity\Ticket;
use App\Entity\TicketTask;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<TicketTask>
 */
class TicketTaskRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, TicketTask::class);
    }

    public function findByIdAndTicket(int $id, Ticket $ticket): ?TicketTask
    {
        return $this->createQueryBuilder('tt')
            ->andWhere('tt.id = :id')
            ->andWhere('tt.ticket = :ticket')
            ->setParameter('id', $id)
            ->setParameter('ticket', $ticket)
            ->getQuery()
            ->getSingleResult()
        ;
    }
}
