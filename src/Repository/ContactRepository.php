<?php declare(strict_types=1);

namespace App\Repository;

use App\Entity\Contact;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\QueryBuilder;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Contact>
 * @implements SearchableRepositoryInterface<Contact>
 */
class ContactRepository extends ServiceEntityRepository implements SearchableRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Contact::class);
    }

    /** @return Contact[] */
    public function search(?string $query = null, array $params = [], ?int $limit = null, int $offset = 0): array
    {
        return $this->buildSearchQuery($query, $params, $limit, $offset)
            ->addSelect('company')
            ->orderBy('c.id', 'DESC')
            ->getQuery()
            ->getResult()
        ;
    }

    public function searchCount(?string $query = null, array $params = []): int
    {
        return (int)$this->buildSearchQuery($query, $params)
            ->select('COUNT(c.id)')
            ->getQuery()
            ->getSingleColumnResult()
        ;
    }

    private function buildSearchQuery(?string $query = null, array $params = [], ?int $limit = null, int $offset = 0): QueryBuilder
    {
        $qb = $this->createQueryBuilder('c')
            ->leftJoin('c.company', 'company')
            ->setMaxResults($limit)
            ->setFirstResult($limit ? $offset : null);
        ;

        if (!empty($query)) {
            $qb->andWhere(
                    $qb->expr()->orX(
                        $qb->expr()->like('LOWER(c.firstName)', 'LOWER(:query)'),
                        $qb->expr()->like('LOWER(c.lastName)', 'LOWER(:query)'),
                        $qb->expr()->like('LOWER(c.address)', 'LOWER(:query)'),
                        $qb->expr()->like('LOWER(company.name)', 'LOWER(:query)'),
                    )
                )
                ->setParameter('query', '%'.$query.'%')
            ;
        }

        return $qb;
    }
}
