<?php declare(strict_types=1);

namespace App\Twig\Components\User;

use App\Entity\Ticket;
use App\Entity\TicketStatus;
use App\Repository\TicketRepository;
use App\Repository\TicketStatusRepository;
use Doctrine\ORM\EntityManagerInterface;
use Psr\Log\LoggerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\UX\LiveComponent\Attribute\AsLiveComponent;
use Symfony\UX\LiveComponent\Attribute\LiveAction;
use Symfony\UX\LiveComponent\Attribute\LiveArg;
use Symfony\UX\LiveComponent\Attribute\LiveListener;
use Symfony\UX\LiveComponent\ComponentToolsTrait;
use Symfony\UX\LiveComponent\DefaultActionTrait;

#[AsLiveComponent]
final class KanbanBoard extends AbstractController
{
    use DefaultActionTrait;
    use ComponentToolsTrait;

    private const MODAL_NAME = 'ticket';

    public function __construct(
        private readonly LoggerInterface $logger,
        private readonly TicketStatusRepository $ticketStatusRepository,
        private readonly TicketRepository $ticketRepository,
        private readonly EntityManagerInterface $entityManager,
    ) {
    }

    /** @return TicketStatus[] */
    public function getStatuses(): array
    {
        return $this->ticketStatusRepository->findAllInDisplayOrder();
    }

    /** @return Ticket[] */
    public function getTickets(): array
    {
        return $this->ticketRepository->findAll();
    }

    #[LiveListener('ticket:update')]
    public function onTicketUpdate(#[LiveArg] int $ticket): void
    {
        // Refresh data
    }

    #[LiveAction]
    public function ticketMove(
        #[LiveArg] int $ticket,
        #[LiveArg] int $targetStatus,
        #[LiveArg] ?int $precedingTicket,
        #[LiveArg] ?int $followingTicket,
    ): void
    {
        /** @var Ticket */
        if (!($currentTicket = $this->ticketRepository->find($ticket))) {
            return;
        }

        /** @var TicketStatus */
        if (!($status = $this->ticketStatusRepository->find($targetStatus))) {
            return;
        }

        $currentTicket->setDisplayOrder(0);
        $currentTicket->setStatus($status);
        $tickets = $status->getTickets();

        $i = 0;
        $found = $precedingTicket === null ? true : false;
        foreach ($tickets as $ticket) {
            if ($precedingTicket !== null && $ticket->getId() === $precedingTicket) {
                $currentTicket->setDisplayOrder($ticket->getDisplayOrder() + 1);
                $found = true;
                continue;
            }

            if (!$found || $ticket->getId() === $currentTicket->getId()) {
                continue;
            }

            $ticket->setDisplayOrder($currentTicket->getDisplayOrder() + $i++ + 1);
        }

        $this->entityManager->flush();
    }

    public function getModalName(): string
    {
        return self::MODAL_NAME;
    }
}
