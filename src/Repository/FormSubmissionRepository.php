<?php declare(strict_types=1);

namespace App\Repository;

use App\Entity\FormDefinition;
use App\Entity\FormSubmission;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/** @extends ServiceEntityRepository<FormSubmission> */
class FormSubmissionRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, FormSubmission::class);
    }

    /** @return FormSubmission[] */
    public function search(FormDefinition $form, string $query, int $limit = 20, int $offset = 0): array
    {
        $sql = <<<SQL
        SELECT id
        FROM form_submission
        WHERE
            search_data @@ websearch_to_tsquery('simple', :query)
            AND form_id = :formId
        ORDER BY submitted_at DESC
        LIMIT :limit
        OFFSET :offset
        SQL;

        $params = [
            'formId' => $form->getId(),
            'query' => $query,
            'limit' => $limit,
            'offset' => $offset,
        ];

        $conn = $this->getEntityManager()->getConnection();
        $ids = $conn->executeQuery($sql, $params)->fetchFirstColumn();
        if (empty($ids)) {
            return [];
        }

        return $this->createQueryBuilder('s')
            ->where('s.id IN (:ids)')
            ->setParameter('ids', $ids)
            ->orderBy('s.submittedAt', 'DESC')
            ->getQuery()
            ->getResult()
        ;
    }

    public function countByQuery(FormDefinition $form, string $query): int
    {
        $conn = $this->getEntityManager()->getConnection();
        $sql = "SELECT COUNT(*) FROM form_submission WHERE form_id = :formId AND search_data @@ websearch_to_tsquery('simple', :query)";
        $params = ['formId' => $form->getId(), 'query' => $query];
        return (int) $conn->executeQuery($sql, $params)->fetchOne();
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
