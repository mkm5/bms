<?php declare(strict_types=1);

namespace App\Repository;

use App\Entity\Document;
use App\Entity\DocumentVersion;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<DocumentVersion>
 */
class DocumentVersionRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, DocumentVersion::class);
    }

    /** @return DocumentVersion[] */
    public function findByDocument(Document $document): array
    {
        return $this->createQueryBuilder('v')
            ->where('v.document = :document')
            ->setParameter('document', $document)
            ->orderBy('v.versionNumber', 'DESC')
            ->getQuery()
            ->getResult()
        ;
    }

    public function findByDocumentAndVersion(Document $document, int $versionNumber): ?DocumentVersion
    {
        return $this->findOneBy([
            'document' => $document,
            'versionNumber' => $versionNumber,
        ]);
    }
}
