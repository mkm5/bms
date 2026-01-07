<?php declare(strict_types=1);

namespace App\Service;

/**
 * @template T
 */
class Paginator
{
    private ?int $totalItems = null;

    /** @var array<T>|null */
    private ?array $items = null;

    /**
     * @param int $currentPage Current page number (1-indexed)
     * @param int $itemsPerPage Number of items per page
     * @param callable(): int $countCallback Callback to count total items
     * @param callable(int, int): array<T> $fetchCallback Callback to fetch items (receives limit and offset)
     */
    public function __construct(
        private readonly int $currentPage,
        private readonly int $itemsPerPage,
        private $countCallback,
        private $fetchCallback,
    ) {
    }

    /** @return array<T> */
    public function getItems(): array
    {
        if ($this->items === null) {
            $offset = ($this->currentPage - 1) * $this->itemsPerPage;
            $this->items = ($this->fetchCallback)($this->itemsPerPage, $offset);
        }
        return $this->items;
    }

    public function getTotalItems(): int
    {
        if ($this->totalItems === null) {
            $this->totalItems = ($this->countCallback)();
        }
        return $this->totalItems;
    }

    public function getTotalPages(): int
    {
        return (int) ceil($this->getTotalItems() / $this->itemsPerPage);
    }

    public function hasPreviousPage(): bool
    {
        return $this->currentPage > 1;
    }

    public function hasNextPage(): bool
    {
        return $this->currentPage < $this->getTotalPages();
    }

    public function getFirstItemNumber(): int
    {
        if ($this->getTotalItems() === 0) {
            return 0;
        }

        return ($this->currentPage - 1) * $this->itemsPerPage + 1;
    }

    public function getLastItemNumber(): int
    {
        return min($this->currentPage * $this->itemsPerPage, $this->getTotalItems());
    }

    /**
     * Generate an array of page numbers for pagination navigation.
     * Returns null values for ellipsis (...) in the pagination.
     *
     * @param int $maxPages Maximum number of page buttons to show
     * @return array<int|null>
     */
    public function getPageNumbers(int $maxPages = 5): array
    {
        $totalPages = $this->getTotalPages();

        if ($totalPages <= 1) {
            return [];
        }

        if ($totalPages <= $maxPages) {
            return range(1, $totalPages);
        }

        // Calculate how many pages we can show in the middle (excluding first and last page)
        $middleSlots = $maxPages - 2;
        $sidePages = (int) floor($middleSlots / 2);

        $rangeStart = max(2, $this->currentPage - $sidePages);
        $rangeEnd = min($totalPages - 1, $this->currentPage + $sidePages);

        // Adjusting range for start
        if ($this->currentPage <= $sidePages + 1) {
            $rangeStart = 2;
            $rangeEnd = min($middleSlots + 1, $totalPages - 1);
        }

        // Adjusting range for end
        if ($this->currentPage >= $totalPages - $sidePages) {
            $rangeStart = max(2, $totalPages - $middleSlots);
            $rangeEnd = $totalPages - 1;
        }

        $pages = [];

        if ($rangeStart > 2) {
            $pages[] = null;
        }

        for ($i = $rangeStart; $i <= $rangeEnd; $i++) {
            $pages[] = $i;
        }

        if ($rangeEnd < $totalPages - 1) {
            $pages[] = null;
        }

        return [1, ...$pages, $totalPages];
    }

    public function getCurrentPage(): int
    {
        return $this->currentPage;
    }

    public function getItemsPerPage(): int
    {
        return $this->itemsPerPage;
    }
}
