<?php declare(strict_types=1);

namespace App\Repository;

use App\Entity\FormDefinition;
use App\Entity\FormSubmission;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\QueryBuilder;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<FormSubmission>
 * @implements SearchableRepository<FormSubmission>
*/
class FormSubmissionRepository extends ServiceEntityRepository implements SearchableRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, FormSubmission::class);
    }

    /** @return FormSubmission[] */
    public function search(?string $query = null, array $params = [], ?int $limit = null, int $offset = 0): array
    {
        return $this->buildSearchQuery($query, $params, $limit, $offset)
            ->join('fs.form', 'f')
            ->addSelect('f')
            ->addOrderBy('fs.submittedAt', 'DESC')
            ->getQuery()
            ->getResult()
        ;
    }

    public function searchCount(?string $query = null, array $params = []): int
    {
        return (int) $this->buildSearchQuery($query, $params)
            ->select('COUNT(fs.id)')
            ->getQuery()
            ->getSingleScalarResult()
        ;
    }


    private function buildSearchQuery(?string $query = null, array $params = [], ?int $limit = null, int $offset = 0): QueryBuilder
    {
        $qb = $this->createQueryBuilder('fs')
            ->setMaxResults($limit)
            ->setFirstResult($limit ? $offset : null)
        ;

        if (!empty($params['form'])) {
            $qb->andWhere('fs.form = :form')
                ->setParameter('form', $params['form'])
            ;
        }

        if (!empty($query)) {
            $qb->andWhere('WORD_SIMILARITY(:query, fs.searchData) > :treshold')
                ->setParameter('query', '%'.$query.'%')
                ->setParameter('treshold', 0.45)
            ;
        }

        return $qb;
    }

    /** @return FormSubmission[] */
    public function findByForm(
        FormDefinition $form,
        ?int $limit = null,
        int $offset = 0,
    ): array
    {
        $qb = $this->createQueryBuilder('s')
            ->where('s.form = :form')
            ->setParameter('form', $form)
            ->orderBy('s.submittedAt', 'DESC');

        if ($limit !== null) {
            $qb->setMaxResults($limit)
                ->setFirstResult($offset);
        }

        return $qb->getQuery()->getResult();
    }

    public function countByForm(FormDefinition $form): int
    {
        return (int) $this->createQueryBuilder('s')
            ->select('COUNT(s.id)')
            ->where('s.form = :form')
            ->setParameter('form', $form)
            ->getQuery()
            ->getSingleScalarResult();
    }

    /** @return array<int, int> formId => count */
    public function countByForms(array $formIds): array
    {
        if (empty($formIds)) {
            return [];
        }

        $result = $this->createQueryBuilder('s')
            ->select('IDENTITY(s.form) as formId, COUNT(s.id) as cnt')
            ->where('s.form IN (:formIds)')
            ->setParameter('formIds', $formIds)
            ->groupBy('s.form')
            ->getQuery()
            ->getArrayResult();

        $counts = [];
        foreach ($result as $row) {
            $counts[(int) $row['formId']] = (int) $row['cnt'];
        }

        return $counts;
    }

    public function delete(FormSubmission $submission): void
    {
        $this->getEntityManager()->remove($submission);
        $this->getEntityManager()->flush();
    }
}
