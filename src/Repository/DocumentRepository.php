<?php declare(strict_types=1);

namespace App\Repository;

use App\Entity\Document;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Document>
 */
class DocumentRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Document::class);
    }

    /** @return Document[] */
    public function search(string $query, int $limit = 20, int $offset = 0): array
    {
        $sql = <<<SQL
        SELECT id
        FROM document
        WHERE search_data @@ websearch_to_tsquery('simple', :query)
        ORDER BY id DESC
        LIMIT :limit
        OFFSET :offset
        SQL;

        $params = [
            'query' => $query,
            'limit' => $limit,
            'offset' => $offset,
        ];

        $conn = $this->getEntityManager()->getConnection();
        $ids = $conn->executeQuery($sql, $params)->fetchFirstColumn();
        if (empty($ids)) {
            return [];
        }

        return $this->createQueryBuilder('d')
            ->leftJoin('d.currentVersion', 'cv')
            ->addSelect('cv')
            ->where('d.id IN (:ids)')
            ->setParameter('ids', $ids)
            ->orderBy('d.id', 'DESC')
            ->getQuery()
            ->getResult()
        ;
    }

    public function countByQuery(string $query): int
    {
        $conn = $this->getEntityManager()->getConnection();
        $sql = "SELECT COUNT(*) FROM document WHERE search_data @@ websearch_to_tsquery('simple', :query)";
        $params = ['query' => $query];
        return (int) $conn->executeQuery($sql, $params)->fetchOne();
    }
}
