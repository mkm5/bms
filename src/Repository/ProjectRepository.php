<?php declare(strict_types=1);

namespace App\Repository;

use App\Entity\Project;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\QueryBuilder;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Project>
 * @implements SearchableRepository<Project>
 */
class ProjectRepository extends ServiceEntityRepository implements SearchableRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Project::class);
    }

    /**
     * @return Project[]
     */
    public function search(?string $query = null, array $params = [], ?int $limit = null, int $offset = 0): array
    {
        $ids = $this->buildSearchQuery($query, $params, $limit, $offset)
            ->select('p.id')
            ->orderBy('p.id', 'DESC')
            ->getQuery()
            ->getResult()
        ;

        return $this->buildSearchQuery($query, $params, $limit, $offset)
            ->join('p.managers', 'm')
            ->addSelect('m')
            ->andWhere('p.id in (:ids)')
            ->setParameter('ids', $ids)
            ->orderBy('p.id', 'DESC')
            ->getQuery()
            ->getResult()
        ;
    }

    public function searchCount(?string $query = null, array $params = []): int
    {
        return (int) $this->buildSearchQuery($query, $params)
            ->select('COUNT(p.id)')
            ->getQuery()
            ->getSingleColumnResult()
        ;
    }

    private function buildSearchQuery(?string $query = null, array $params = [], ?int $limit = null, int $offset = 0): QueryBuilder
    {
        $qb = $this->createQueryBuilder('p')
            ->setMaxResults($limit)
            ->setFirstResult($limit ? $offset : null)
        ;

        if (!empty($query)) {
            $qb
                ->andWhere('LOWER(p.name) LIKE :query')
                ->setParameter('query', '%' . strtolower($query) . '%')
            ;
        }

        if (!empty($params['onlyFinished']) && $params['onlyFinished']) {
            $qb
                ->andWhere('p.isFinished = :isFinished')
                ->setParameter('isFinished', true)
            ;
        }

        return $qb;
    }
}
