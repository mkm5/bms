<?php declare(strict_types=1);

namespace App\Repository;

/**
 * @template T of object
 */
interface SearchableRepository
{
    /** @return T[] */
    public function search(?string $query = null, array $params = [], ?int $limit = null, int $offset = 0): array;

    public function searchCount(?string $query = null, array $params = []): int;
}
