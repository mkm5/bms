<?php declare(strict_types=1);

namespace App\Repository;

use App\DTO\TicketTaskCount;
use App\DTO\TicketWithTaskCount;
use App\Entity\Project;
use App\Entity\Ticket;
use App\Entity\TicketStatus;
use App\Entity\TicketTask;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\QueryBuilder;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Ticket>
 * @implements SearchableRepository<Ticket>
 */
class TicketRepository extends ServiceEntityRepository implements SearchableRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Ticket::class);
    }

    /**
     * @return Ticket[]
     */
    public function findByStatusInDisplayOrder(TicketStatus $status, bool $includeArchived = false): array
    {
        $qb = $this->createQueryBuilder('t');

        if (!$includeArchived) {
            $qb = $qb->andWhere('t.isArchived = false');
        }

        return $qb
            ->andWhere('t.status = :status')
            ->setParameter('status', $status)
            ->orderBy('t.displayOrder', 'ASC')
            ->getQuery()
            ->getResult()
        ;
    }

    /** @return Ticket[] */
    public function findAllNonArchivedForKanban(): array
    {
        return $this->createQueryBuilder('t')
            ->leftJoin('t.project', 'p')
            ->addSelect('p')
            ->leftJoin('t.tags', 'tag')
            ->addSelect('tag')
            ->leftJoin('t.status', 's')
            ->addSelect('s')
            ->where('t.isArchived = false')
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

    /** @return Ticket[] */
    public function findByProject(Project $project): array
    {
        return $this->createQueryBuilder('t')
            ->where('t.project = :project')
            ->setParameter('project', $project)
            ->getQuery()
            ->getResult()
        ;
    }

    /** @return TicketWithTaskCount[] */
    public function search(?string $query = null, array $params = [], ?int $limit = null, int $offset = 0): array
    {
        /** @var Ticket[] $tickets */
        $tickets = $this->buildSearchQuery($query, $params, $limit, $offset)
            ->leftJoin('t.project', 'p')
            ->addSelect('p')
            ->orderBy('t.id', 'DESC')
            ->getQuery()
            ->getResult()
        ;

        if (empty($tickets)) {
            return [];
        }

        $ticketIds = array_map(fn(Ticket $t) => $t->getId(), $tickets);

        /** @var TicketTaskCount[] */
        $taskCounts = $this->getEntityManager()
            ->createQueryBuilder()
            ->select(sprintf(
                'NEW %s(
                    IDENTITY(task.ticket),
                    COUNT(task.id),
                    SUM(CASE WHEN task.isFinished = true THEN 1 ELSE 0 END)
                )',
                TicketTaskCount::class
            ))
            ->from(TicketTask::class, 'task')
            ->where('task.ticket IN (:ticketIds)')
            ->setParameter('ticketIds', $ticketIds)
            ->groupBy('task.ticket')
            ->getQuery()
            ->getResult()
        ;

        $taskCountMap = [];
        foreach ($taskCounts as $row) {
            $taskCountMap[$row->ticket] = $row;
        }

        return array_map(
            fn(Ticket $ticket) => new TicketWithTaskCount(
                $ticket,
                $taskCountMap[$ticket->getId()]->total ?? 0,
                $taskCountMap[$ticket->getId()]->completed ?? 0,
            ),
            $tickets,
        );
    }

    public function searchCount(?string $query = null, array $params = []): int
    {
        return (int) $this->buildSearchQuery($query, $params)
            ->select('COUNT(t.id)')
            ->getQuery()
            ->getSingleScalarResult()
        ;
    }

    private function buildSearchQuery(?string $query = null, array $params = [], ?int $limit = null, int $offset = 0): QueryBuilder
    {
        $qb = $this->createQueryBuilder('t')
            ->leftJoin('t.status', 's')
            ->addSelect('s')
            ->setMaxResults($limit)
            ->setFirstResult($limit ? $offset : null)
        ;

        if (key_exists('project', $params)) {
            $qb->andWhere('t.project = :project')
                ->setParameter('project', $params['project'])
            ;
        }

        if ($query !== null && $query !== '') {
            $qb->andWhere('t.title LIKE :query OR t.description LIKE :query OR s.name LIKE :query')
                ->setParameter('query', '%' . $query . '%')
            ;
        }

        return $qb;
    }
}
