<?php declare(strict_types=1);

namespace App\Repository;

use App\Entity\FormDefinition;
use App\Entity\Project;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/** @extends ServiceEntityRepository<FormDefinition> */
class FormDefinitionRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, FormDefinition::class);
    }

    /**
     * @return FormDefinition[]
     */
    public function search(
        ?string $search = null,
        ?Project $project = null,
        ?int $limit = null,
        int $offset = 0,
    ): array {
        $qb = $this->createQueryBuilder('f')
            ->leftJoin('f.project', 'project')
            ->addSelect('project')
            ->orderBy('f.id', 'DESC');

        if ($search !== null && $search !== '') {
            $qb->andWhere('LOWER(f.name) LIKE LOWER(:search)')
                ->setParameter('search', '%' . $search . '%');
        }

        if ($project !== null) {
            $qb->andWhere('f.project = :project')
                ->setParameter('project', $project);
        }

        if ($limit !== null) {
            $qb->setMaxResults($limit);
        }

        $qb->setFirstResult($offset);

        return $qb->getQuery()->getResult();
    }

    public function countSearch(?string $search = null, ?Project $project = null): int
    {
        $qb = $this->createQueryBuilder('f')
            ->select('COUNT(f.id)');

        if ($search !== null && $search !== '') {
            $qb->andWhere('LOWER(f.name) LIKE LOWER(:search)')
                ->setParameter('search', '%' . $search . '%');
        }

        if ($project !== null) {
            $qb->andWhere('f.project = :project')
                ->setParameter('project', $project);
        }

        return (int) $qb->getQuery()->getSingleScalarResult();
    }
}
