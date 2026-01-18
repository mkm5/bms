<?php declare(strict_types=1);

namespace App\Service;

trait PaginatorTrait
{
    abstract protected function getPaginator(): Paginator;

    private ?array $_items = null;

    private ?int $_totalItems = null;

    public function getItems(): array {
        return $this->_items ??= $this->getPaginator()->getItems();
    }

    public function getTotalItems(): int {
        return $this->_totalItems ??= $this->getPaginator()->getTotalItems();
    }

    public function getTotalPages(): int { return $this->getPaginator()->getTotalPages(); }
    public function hasPreviousPage(): bool { return $this->getPaginator()->hasPreviousPage(); }
    public function hasNextPage(): bool { return $this->getPaginator()->hasNextPage(); }
    public function getFirstItemNumber(): int { return $this->getPaginator()->getFirstItemNumber(); }
    public function getLastItemNumber(): int { return $this->getPaginator()->getLastItemNumber(); }
    public function getPageNumbers(int $maxPages = 5): array { return $this->getPaginator()->getPageNumbers($maxPages); }
}
