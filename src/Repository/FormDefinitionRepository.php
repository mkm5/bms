<?php declare(strict_types=1);

namespace App\Repository;

use App\DTO\FormWithStatistics;
use App\Entity\FormDefinition;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\QueryBuilder;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<FormDefinition>
 * @implements SearchableRepository<FormDefinition>
 */
class FormDefinitionRepository extends ServiceEntityRepository implements SearchableRepository
{
    public function __construct(
        ManagerRegistry $registry,
        private readonly FormFieldRepository $formFieldRepository,
        private readonly FormSubmissionRepository $formSubmissionRepository,
    ) {
        parent::__construct($registry, FormDefinition::class);
    }

    /** @return FormWithStatistics[] */
    public function search(?string $query = null, array $params = [], ?int $limit = null, int $offset = 0): array
    {
        $forms = $this->buildSearchQuery($query, $params, $limit, $offset)
            ->leftJoin('f.project', 'p')
            ->addSelect('p')
            ->orderBy('f.id', 'DESC')
            ->getQuery()
            ->getResult()
        ;


        $formsIds = array_map(fn(FormDefinition $f) => $f->getId(), $forms);
        $fieldsByForms = $this->formFieldRepository->countByForms($formsIds);
        $submissionsByForms = $this->formSubmissionRepository->countByForms($formsIds);

        return array_map(
            fn(FormDefinition $form) => new FormWithStatistics(
                $form,
                $fieldsByForms[$form->getId()],
                $submissionsByForms[$form->getId()],
            ),
            $forms,
        );
    }

    public function searchCount(?string $query = null, array $params = []): int
    {
        return (int) $this->buildSearchQuery($query, $params)
            ->select('COUNT(f.id)')
            ->getQuery()
            ->getSingleScalarResult()
        ;
    }

    private function buildSearchQuery(?string $query = null, array $params = [], ?int $limit = null, int $offset = 0): QueryBuilder
    {
        $qb = $this->createQueryBuilder('f')
            ->setMaxResults($limit)
            ->setFirstResult($limit ? $offset : null);
        ;

        if (!empty($query)) {
            $qb->andWhere('LOWER(f.name) LIKE LOWER(:query)')
                ->setParameter('query', $query)
            ;
        }

        if (!empty($params['project'])) {
            $qb->andWhere('f.project = :project')
                ->setParameter('project', $params['project'])
            ;
        }

        return $qb;
    }
}
