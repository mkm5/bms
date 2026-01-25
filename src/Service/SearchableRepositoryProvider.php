<?php declare(strict_types=1);

namespace App\Service;

use App\Repository\SearchableRepository;
use Doctrine\Persistence\ManagerRegistry;
use InvalidArgumentException;

class SearchableRepositoryProvider
{
    public function __construct(
        private readonly ManagerRegistry $managerRegistry,
    ) {
    }

    public function getRepository(string $className): SearchableRepository
    {
        $repository = $this->managerRegistry->getRepository($className);
        if (!$repository instanceof SearchableRepository) {
            throw new InvalidArgumentException(sprintf(
                'Repository for %s must implement %s',
                $className,
                SearchableRepository::class,
            ));
        }

        return $repository;
    }
}
