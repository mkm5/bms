<?php declare(strict_types=1);

namespace App\Repository;

use App\Entity\Contact;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Contact>
 */
class ContactRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Contact::class);
    }

    /**
     * @return Contact[]
     */
    public function search(
        ?string $search = null,
        ?int $limit = null,
        int $offset = 0,
    ): array {
        $qb = $this->createQueryBuilder('c')
            ->leftJoin('c.company', 'company')
            ->addSelect('company')
            ->orderBy('c.id', 'DESC')
        ;

        if ($search !== null && $search !== '') {
            $qb->andWhere(
                    $qb->expr()->orX(
                        $qb->expr()->like('c.firstName', ':search'),
                        $qb->expr()->like('c.lastName', ':search'),
                        $qb->expr()->like('c.address', ':search'),
                        $qb->expr()->like('company.name', ':search'),
                    )
                )
                ->setParameter('search', '%'.$search.'%')
            ;
        }

        if ($limit !== null) {
            $qb->setMaxResults($limit)
                ->setFirstResult($offset)
            ;
        }

        return $qb->getQuery()->getResult();
    }

    public function countSearch(?string $search = null): int
    {
        $qb = $this->createQueryBuilder('c')
            ->select('COUNT(c.id)')
            ->leftJoin('c.company', 'company');

        if ($search !== null && $search !== '') {
            $qb->andWhere('LOWER(c.firstName) LIKE LOWER(:search) OR LOWER(c.lastName) LIKE LOWER(:search) OR LOWER(company.name) LIKE LOWER(:search)')
                ->setParameter('search', '%' . $search . '%');
        }

        return (int) $qb->getQuery()->getSingleScalarResult();
    }
}
