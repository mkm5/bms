<?php declare(strict_types=1);

namespace App\Repository;

use App\Entity\Company;
use App\Listing\SearchableRepositoryInterface;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\QueryBuilder;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Company>
 * @implements SearchableRepositoryInterface<Company>
 */
class CompanyRepository extends ServiceEntityRepository implements SearchableRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Company::class);
    }

    public function countForListing(array $criteria = []): int
    {
        return $this->countSearch($criteria['search'] ?? null);
    }

    /**
     * @return Company[]
     */
    public function fetchForListing(array $criteria = [], int $limit = 20, int $offset = 0): array
    {
        return $this->search($criteria['search'] ?? null, [], $limit, $offset);
    }

    /**
     * @return Company[]
     */
    public function search(?string $query = null, array $params = [], ?int $limit = null, int $offset = 0): array
    {
        return $this->buildSearchQuery($query, $params, $limit, $offset)
            ->orderBy('c.id', 'DESC')
            ->getQuery()
            ->getResult()
        ;
    }

    public function searchCount(?string $query = null, array $params = []): int
    {
        return (int) $this->buildSearchQuery($query, $params)
            ->select('COUNT(c.id)')
            ->getQuery()
            ->getSingleScalarResult()
        ;
    }

    private function buildSearchQuery(?string $query = null, array $params = [], ?int $limit = null, int $offset = 0): QueryBuilder
    {
        $qb = $this->createQueryBuilder('c')
            ->setMaxResults($limit)
            ->setFirstResult($limit ? $offset : null)
        ;

        if (!empty($query)) {
            $qb->andWhere(
                    $qb->expr()->orX(
                        $qb->expr()->like('LOWER(c.name)', 'LOWER(:query)'),
                        $qb->expr()->like('LOWER(c.address)', 'LOWER(:query)')
                    )
                )
                ->setParameter('query', '%' . $query . '%')
            ;
        }

        return $qb;
    }
}
