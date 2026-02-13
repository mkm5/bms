<?php declare(strict_types=1);

namespace App\Tests\Service;

use App\Entity\TicketStatus;
use App\Repository\TicketStatusRepository;
use App\Service\TicketStatusMover;
use PHPUnit\Framework\TestCase;
use ValueError;

final class TicketStatusMoverTest extends TestCase
{
    private TicketStatusRepository $ticketStatusRepository;
    private TicketStatusMover $mover;

    protected function setUp(): void
    {
        $this->ticketStatusRepository = $this->createStub(TicketStatusRepository::class);
        $this->mover = new TicketStatusMover($this->ticketStatusRepository);
    }

    private function createStatus(int $id, int $displayOrder = 0): TicketStatus
    {
        return TicketStatus::create('Status ' . $id)->setId($id)->setDisplayOrder($displayOrder);
    }

    /** @return array<int, int> status ID => displayOrder */
    private function extractOrder(array $statuses): array
    {
        $result = [];
        foreach ($statuses as $status) {
            $result[$status->getId()] = $status->getDisplayOrder();
        }
        return $result;
    }

    public function testMoveToBeginning(): void
    {
        $a = $this->createStatus(1, 0);
        $b = $this->createStatus(2, 1);
        $c = $this->createStatus(3, 2);
        $statuses = [$a, $b, $c];
        $this->ticketStatusRepository->method('findAllInDisplayOrder')->willReturn($statuses);
        $this->mover->move($c);
        $this->assertSame([1 => 1, 2 => 2, 3 => 0], $this->extractOrder($statuses));
    }

    public function testMoveAfterSpecificStatus(): void
    {
        $a = $this->createStatus(1, 0);
        $b = $this->createStatus(2, 1);
        $c = $this->createStatus(3, 2);
        $statuses = [$a, $b, $c];
        $this->ticketStatusRepository->method('findAllInDisplayOrder')->willReturn($statuses);
        $this->mover->move($c, $a);
        $this->assertSame([1 => 0, 2 => 2, 3 => 1], $this->extractOrder($statuses));
    }

    public function testMoveWithIntIds(): void
    {
        $a = $this->createStatus(1, 0);
        $b = $this->createStatus(2, 1);
        $c = $this->createStatus(3, 2);
        $statuses = [$a, $b, $c];
        $this->ticketStatusRepository->method('findAllInDisplayOrder')->willReturn($statuses);
        $this->mover->move($c->getId(), $a->getId());
        $this->assertSame([1 => 0, 2 => 2, 3 => 1], $this->extractOrder($statuses));
    }

    public function testMoveToSamePositionIsNoop(): void
    {
        $a = $this->createStatus(1, 0);
        $b = $this->createStatus(2, 1);
        $c = $this->createStatus(3, 2);
        $statuses = [$a, $b, $c];
        $this->ticketStatusRepository->method('findAllInDisplayOrder')->willReturn($statuses);
        $this->mover->move($b, $a);
        $this->assertSame([1 => 0, 2 => 1, 3 => 2], $this->extractOrder($statuses));
    }

    public function testThrowsWhenStatusNotFound(): void
    {
        $this->ticketStatusRepository->method('findAllInDisplayOrder')->willReturn([]);
        $this->expectException(ValueError::class);
        $this->mover->move(999);
    }
}
