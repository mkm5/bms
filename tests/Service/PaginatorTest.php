<?php declare(strict_types=1);

namespace App\Tests\Service;

use App\Service\Paginator;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

final class PaginatorTest extends TestCase
{
    private function createPaginator(int $currentPage, int $itemsPerPage, int $totalItems): Paginator
    {
        $items = array_map(fn (int $i) => (string)$i, range(1, $totalItems));
        return new Paginator(
            $currentPage,
            $itemsPerPage,
            fn () => $totalItems,
            fn (int $limit, int $offset) => array_slice($items, $offset, $limit),
        );
    }

    public function testGetItemsReturnsCorrectSlice(): void
    {
        $paginator = $this->createPaginator(currentPage: 2, itemsPerPage: 3, totalItems: 10);
        $this->assertSame(['4', '5', '6'], $paginator->getItems());
    }

    public function testGetItemsLastPagePartial(): void
    {
        $paginator = $this->createPaginator(currentPage: 3, itemsPerPage: 4, totalItems: 10);
        $this->assertSame(['9', '10'], $paginator->getItems());
    }

    public function testGetTotalPages(): void
    {
        $this->assertSame(4, $this->createPaginator(1, 3, 10)->getTotalPages());
        $this->assertSame(2, $this->createPaginator(1, 5, 10)->getTotalPages());
        $this->assertSame(1, $this->createPaginator(1, 10, 10)->getTotalPages());
        $this->assertSame(1, $this->createPaginator(1, 10, 1)->getTotalPages());
    }

    public function testHasPreviousPage(): void
    {
        $this->assertFalse($this->createPaginator(1, 10, 30)->hasPreviousPage());
        $this->assertTrue($this->createPaginator(3, 10, 30)->hasPreviousPage());
    }

    public function testHasNextPage(): void
    {
        $this->assertTrue($this->createPaginator(1, 10, 30)->hasNextPage());
        $this->assertFalse($this->createPaginator(3, 10, 30)->hasNextPage());
    }

    public function testFirstAndLastItemNumber(): void
    {
        $paginator = $this->createPaginator(currentPage: 2, itemsPerPage: 10, totalItems: 25);
        $this->assertSame(11, $paginator->getFirstItemNumber());
        $this->assertSame(20, $paginator->getLastItemNumber());
    }

    public function testFirstAndLastItemNumberLastPage(): void
    {
        $paginator = $this->createPaginator(currentPage: 3, itemsPerPage: 10, totalItems: 25);
        $this->assertSame(21, $paginator->getFirstItemNumber());
        $this->assertSame(25, $paginator->getLastItemNumber());
    }

    public function testFirstItemNumberIsZeroWhenEmpty(): void
    {
        $paginator = $this->createPaginator(currentPage: 1, itemsPerPage: 10, totalItems: 0);
        $this->assertSame(0, $paginator->getFirstItemNumber());
        $this->assertSame(0, $paginator->getLastItemNumber());
    }

    public function testGetPageNumbersReturnsEmptyForSinglePage(): void
    {
        $this->assertSame([], $this->createPaginator(1, 10, 10)->getPageNumbers());
        $this->assertSame([], $this->createPaginator(1, 10, 0)->getPageNumbers());
    }

    public function testGetPageNumbersReturnsAllWhenFewPages(): void
    {
        $paginator = $this->createPaginator(currentPage: 1, itemsPerPage: 10, totalItems: 30);
        $this->assertSame([1, 2, 3], $paginator->getPageNumbers(5));
    }

    #[DataProvider('pageNumbersProvider')]
    public function testGetPageNumbers(int $currentPage, int $totalItems, int $maxPages, array $expected): void
    {
        $paginator = $this->createPaginator($currentPage, itemsPerPage: 10, totalItems: $totalItems);
        $this->assertSame($expected, $paginator->getPageNumbers($maxPages));
    }

    /** @return iterable<string, array{int, int, int, array<int|null>}> */
    public static function pageNumbersProvider(): iterable
    {
        yield 'many pages, in middle' => [10, 200, 5, [1, null, 9, 10, 11, null, 20]];
        yield 'many pages, near start' => [3, 200, 5, [1, 2, 3, 4, null, 20]];
        yield 'many pages, near end' => [18, 200, 5, [1, null, 17, 18, 19, 20]];
        yield 'exact max pages' => [3, 50, 5, [1, 2, 3, 4, 5]];
        yield 'many pages, wider window' => [10, 200, 7, [1, null, 8, 9, 10, 11, 12, null, 20]];
    }
}
