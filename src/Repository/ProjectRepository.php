<?php declare(strict_types=1);

namespace App\Repository;

use App\Entity\Project;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\QueryBuilder;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Project>
 */
class ProjectRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Project::class);
    }

    /**
     * @return Project[]
     */
    public function search(
        ?string $search = null,
        bool $onlyFinished = false,
        ?int $limit = null,
        int $offset = 0,
    ): array
    {
        $qb = $this->createQueryBuilder('p');

        $this->applyListingSearchFilters($qb, $search, $onlyFinished);

        $qb->orderBy('p.id', 'DESC');

        if ($limit !== null) $qb->setMaxResults($limit);
        if ($offset > 0) $qb->setFirstResult($offset);

        return $qb->getQuery()->getResult();
    }

    public function countSearch(
        ?string $search = null,
        bool $onlyFinished = false,
    ): int
    {
        $qb = $this->createQueryBuilder('p')->select('COUNT(p.id)');
        $this->applyListingSearchFilters($qb, $search, $onlyFinished);
        return (int) $qb->getQuery()->getSingleScalarResult();
    }

    private function applyListingSearchFilters(
        QueryBuilder $qb,
        ?string $search,
        bool $onlyFinished,
    ): void
    {
        if (!empty($search)) {
            $qb
                ->andWhere('LOWER(p.name) LIKE :search')
                ->setParameter('search', '%' . strtolower($search) . '%')
            ;
        }

        if ($onlyFinished) {
            $qb
                ->andWhere('p.isFinished = :isFinished')
                ->setParameter('isFinished', true)
            ;
        }
    }
}
