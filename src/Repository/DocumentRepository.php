<?php declare(strict_types=1);

namespace App\Repository;

use App\Entity\Document;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\QueryBuilder;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Document>
 * @implements SearchableRepository<Document>
 */
class DocumentRepository extends ServiceEntityRepository implements SearchableRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Document::class);
    }

    /** @return Document[] */
    public function findPaginated(int $limit = 20, int $offset = 0): array
    {
        return $this->createQueryBuilder('d')
            ->leftJoin('d.currentVersion', 'cv')
            ->addSelect('cv')
            ->orderBy('d.id', 'DESC')
            ->setMaxResults($limit)
            ->setFirstResult($offset)
            ->getQuery()
            ->getResult()
        ;
    }

    public function countAll(): int
    {
        return (int) $this->createQueryBuilder('d')
            ->select('COUNT(d.id)')
            ->getQuery()
            ->getSingleScalarResult()
        ;
    }

    /**
     * @return Document[]
     */
    public function search(?string $query = null, array $params = [], ?int $limit = null, int $offset = 0): array
    {
        return $this->buildSearchQuery($query, $params, $limit, $offset)
            ->leftJoin('d.currentVersion', 'cv')
            ->addSelect('cv')
            ->orderBy('d.id', 'DESC')
            ->getQuery()
            ->getResult()
        ;
    }

    public function searchCount(?string $query = null, array $params = []): int
    {
        return (int) $this->buildSearchQuery($query, $params)
            ->select('COUNT(d.id)')
            ->getQuery()
            ->getSingleScalarResult()
        ;
    }

    private function buildSearchQuery(?string $query = null, array $params = [], ?int $limit = null, int $offset = 0): QueryBuilder
    {
        $qb = $this->createQueryBuilder('d')
            ->setMaxResults($limit)
            ->setFirstResult($limit ? $offset : null)
        ;

        if (!empty($query)) {
            $qb->andWhere('WORD_SIMILARITY(:query, d.searchData) > :treshold')
                ->setParameter('query', '%'.$query.'%')
                ->setParameter('treshold', 0.45)
            ;
        }

        return $qb;
    }
}
