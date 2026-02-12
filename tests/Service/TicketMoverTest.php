<?php declare(strict_types=1);

namespace App\Tests\Service;

use App\Entity\Ticket;
use App\Entity\TicketStatus;
use App\Repository\TicketRepository;
use App\Repository\TicketStatusRepository;
use App\Service\TicketMover;
use PHPUnit\Framework\TestCase;
use ReflectionProperty;
use ValueError;

final class TicketMoverTest extends TestCase
{
    private TicketRepository $ticketRepository;
    private TicketStatusRepository $ticketStatusRepository;
    private TicketMover $mover;

    protected function setUp(): void
    {
        $this->ticketRepository = $this->createStub(TicketRepository::class);
        $this->ticketStatusRepository = $this->createStub(TicketStatusRepository::class);
        $this->mover = new TicketMover($this->ticketStatusRepository, $this->ticketRepository);
    }

    private function createTicket(int $id, int $displayOrder = 0): Ticket
    {
        $ticket = new Ticket();
        (new ReflectionProperty(Ticket::class, 'id'))->setValue($ticket, $id);
        $ticket->setDisplayOrder($displayOrder);

        return $ticket;
    }

    private function createStatus(int $id): TicketStatus
    {
        return TicketStatus::create('Status ' . $id)->setId($id);
    }

    /** @return array<int, int> ticket ID => displayOrder */
    private function extractOrder(array $tickets): array
    {
        $result = [];
        foreach ($tickets as $ticket) {
            $result[$ticket->getId()] = $ticket->getDisplayOrder();
        }

        return $result;
    }

    public function testMoveToBeginningOfStatus(): void
    {
        $status = $this->createStatus(1);
        $ticket = $this->createTicket(99);
        $existing = [
            $a = $this->createTicket(1, 0),
            $b = $this->createTicket(2, 1),
            $c = $this->createTicket(3, 2),
        ];

        $this->ticketRepository->method('findByStatusInDisplayOrder')->willReturn($existing);

        $this->mover->move($ticket, $status, null);

        $this->assertSame(0, $ticket->getDisplayOrder());
        $this->assertSame([1 => 1, 2 => 2, 3 => 3], $this->extractOrder($existing));
    }

    public function testMoveAfterSpecificTicket(): void
    {
        $status = $this->createStatus(1);
        $ticket = $this->createTicket(99);
        $existing = [$this->createTicket(1, 0), $this->createTicket(2, 1), $this->createTicket(3, 2)];
        $this->ticketRepository->method('findByStatusInDisplayOrder')->willReturn($existing);
        $this->mover->move($ticket, $status, 2);
        $this->assertSame(2, $ticket->getDisplayOrder());
        $this->assertSame([1 => 0, 2 => 1, 3 => 3], $this->extractOrder($existing));
    }

    public function testMoveWithinSameStatusAfterTicket(): void
    {
        $status = $this->createStatus(1);
        $ticket = $this->createTicket(1, 0);
        $b = $this->createTicket(2, 1);
        $c = $this->createTicket(3, 2);
        $this->ticketRepository->method('findByStatusInDisplayOrder')->willReturn([$ticket, $b, $c]);
        $this->mover->move($ticket, $status, 2);
        $this->assertSame(2, $ticket->getDisplayOrder());
        $this->assertSame(1, $b->getDisplayOrder());
        $this->assertSame(3, $c->getDisplayOrder());
    }

    public function testResolvesTicketAndStatusFromIds(): void
    {
        $status = $this->createStatus(1);
        $ticket = $this->createTicket(5);
        $this->ticketRepository->method('find')->willReturn($ticket);
        $this->ticketStatusRepository->method('find')->willReturn($status);
        $this->ticketRepository->method('findByStatusInDisplayOrder')->willReturn([]);
        $this->mover->move(5, 1, null);
        $this->assertSame($status, $ticket->getStatus());
    }

    public function testThrowsWhenTicketNotFound(): void
    {
        $this->ticketRepository->method('find')->willReturn(null);
        $this->expectException(ValueError::class);
        $this->expectExceptionMessage('Ticket not found');
        $this->mover->move(999, $this->createStatus(1), null);
    }

    public function testThrowsWhenStatusNotFound(): void
    {
        $this->ticketStatusRepository->method('find')->willReturn(null);
        $this->expectException(ValueError::class);
        $this->expectExceptionMessage('Ticket status not found');
        $this->mover->move($this->createTicket(1), 999, null);
    }
}
