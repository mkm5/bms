<?php declare(strict_types=1);

namespace App\Twig\Components\User;

use App\Entity\Ticket;
use App\Entity\TicketTask;
use App\Form\TicketType;
use App\Repository\TicketRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Form\FormInterface;
use Symfony\UX\LiveComponent\Attribute\AsLiveComponent;
use Symfony\UX\LiveComponent\Attribute\LiveAction;
use Symfony\UX\LiveComponent\Attribute\LiveArg;
use Symfony\UX\LiveComponent\Attribute\LiveListener;
use Symfony\UX\LiveComponent\Attribute\LiveProp;
use Symfony\UX\LiveComponent\ComponentToolsTrait;
use Symfony\UX\LiveComponent\ComponentWithFormTrait;
use Symfony\UX\LiveComponent\DefaultActionTrait;

#[AsLiveComponent]
final class TicketViewEdit extends AbstractController
{
    use ComponentToolsTrait;
    use ComponentWithFormTrait;
    use DefaultActionTrait;

    #[LiveProp]
    public string $editModalName = 'ticket';

    #[LiveProp]
    public string $status = 'edit';

    #[LiveProp]
    public ?Ticket $viewTicket = null;

    public function __construct(
        private readonly TicketRepository $ticketRepository,
        private readonly EntityManagerInterface $em,
    ) {
    }

    protected function instantiateForm(): FormInterface
    {
        return $this->createForm(TicketType::class, $this->viewTicket);
    }

    #[LiveAction]
    public function save(): void
    {
        $this->submitForm();

        /** @var Ticket */
        $ticket = $this->getForm()->getData();
        $this->em->persist($ticket);
        foreach ($ticket->getTasks() as $task) {
            $this->em->persist($task);
        }
        $this->em->flush();

        $this->dispatchBrowserEvent('modal:close', ['id' => $this->editModalName]);
        $this->emit('ticket:update', ['ticket' => $ticket->getId()]);
        $this->viewTicket = null;
        $this->resetForm();
    }

    #[LiveListener('ticket:edit')]
    public function onTicketEdit(#[LiveArg] ?int $ticket = null): void
    {
        $this->status = 'edit';
        $this->viewTicket = !$ticket
            ? null
            : $this->ticketRepository->find($ticket)
        ;

        if ($ticket && !$this->viewTicket) {
            throw new \ValueError('Ticket with id "'.($ticket).'" does not exist');
        }

        $this->dispatchBrowserEvent('modal:open', ['id' => $this->editModalName]);
        $this->resetForm();
    }

    #[LiveListener('ticket:view')]
    public function onTicketView(#[LiveArg] int $ticket): void
    {
        $this->status = 'view';
        $this->viewTicket = $this->ticketRepository->find($ticket);
        if (!$this->viewTicket) {
            throw new \ValueError('Ticket with id "'.($ticket).'" does not exist');
        }

        $this->dispatchBrowserEvent('modal:open', ['id' => $this->editModalName]);
        $this->resetForm();
    }

    #[LiveAction]
    public function addTask(): void
    {
        $this->formValues['tasks'][] = [];
    }

    #[LiveAction]
    public function removeTask(#[LiveArg] int $index): void
    {
        unset($this->formValues['tasks'][$index]);
    }

    #[LiveAction]
    public function toggleTask(#[LiveArg] int $taskId): void
    {
        if (!$this->viewTicket) {
            return;
        }

        /** @var TicketTask */
        foreach ($this->viewTicket->getTasks() as $task) {
            if ($task->getId() === $taskId) {
                $task->toggleFinished();
                $this->em->flush();

                /**
                 * NOTE:
                 * LiveComponent tries to hydrate entities from dehydrated state, not fresh from DB.
                 * It is necessary to force a re-fetch.
                 */
                $this->resetForm();
            }
        }
    }
}
