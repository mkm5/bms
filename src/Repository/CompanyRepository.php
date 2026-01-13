<?php

declare(strict_types=1);

namespace App\Repository;

use App\Entity\Company;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Company>
 */
class CompanyRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Company::class);
    }

    /**
     * @return Company[]
     */
    public function search(
        ?string $search = null,
        ?int $limit = null,
        int $offset = 0,
    ): array {
        $qb = $this->createQueryBuilder('c')
            ->orderBy('c.id', 'DESC');

        if ($search !== null && $search !== '') {
            $qb->andWhere('LOWER(c.name) LIKE LOWER(:search)')
                ->setParameter('search', '%' . $search . '%');
        }

        if ($limit !== null) {
            $qb->setMaxResults($limit)
                ->setFirstResult($offset);
        }

        return $qb->getQuery()->getResult();
    }

    public function countSearch(?string $search = null): int
    {
        $qb = $this->createQueryBuilder('c')
            ->select('COUNT(c.id)');

        if ($search !== null && $search !== '') {
            $qb->andWhere('LOWER(c.name) LIKE LOWER(:search)')
                ->setParameter('search', '%' . $search . '%');
        }

        return (int) $qb->getQuery()->getSingleScalarResult();
    }
}
