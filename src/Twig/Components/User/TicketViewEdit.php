<?php declare(strict_types=1);

namespace App\Twig\Components\User;

use App\Entity\Ticket;
use App\Form\TicketType;
use App\Repository\TagRepository;
use App\Repository\TicketRepository;
use App\Repository\TicketTaskRepository;
use App\Security\Voter\TicketVoter;
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
use ValueError;

#[AsLiveComponent]
final class TicketViewEdit extends AbstractController
{
    use ComponentToolsTrait;
    use ComponentWithFormTrait;
    use DefaultActionTrait;

    #[LiveProp]
    public string $editModalName = 'ticket';

    #[LiveProp]
    public string $status = 'view';

    #[LiveProp]
    public ?Ticket $viewTicket = null;

    #[LiveProp]
    public string $tagCreateModalName = 'tag-create';

    public function __construct(
        private readonly TagRepository $tagRepository,
        private readonly TicketRepository $ticketRepository,
        private readonly TicketTaskRepository $taskRepository,
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
        if ($this->viewTicket?->getId()) {
            $this->denyAccessUnlessGranted(TicketVoter::EDIT, $this->viewTicket);
        }

        $this->submitForm();

        /** @var Ticket */
        $ticket = $this->getForm()->getData();
        $this->em->persist($ticket);
        $this->em->flush();

        $this->dispatchBrowserEvent('modal:close', ['id' => $this->editModalName]);
        $this->emit('ticket:update', ['ticket' => $ticket->getId()]);
        $this->emit('listing:refresh');
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

        if ($ticket) {
            if (!$this->viewTicket) {
                throw new \ValueError('Ticket with id "'.($ticket).'" does not exist');
            }
            $this->denyAccessUnlessGranted(TicketVoter::EDIT, $this->viewTicket);
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
    #[LiveListener('ticket:toggle-archive')]
    public function toggleArchive(#[LiveArg] int $ticket): void
    {
        $ticket = $this->ticketRepository->find($ticket);
        if (!$ticket || !$this->getAccessDecision(TicketVoter::ARCHIVE, $ticket)) {
            return;
        }

        $ticket->setIsArchived(!$ticket->isArchived());
        $this->em->flush();
        $this->emit('ticket:update', ['ticket' => $ticket->getId()]);
        $this->emit('listing:refresh');
    }

    #[LiveAction]
    public function addTask(): void
    {
        $this->denyAccessUnlessGranted(TicketVoter::EDIT, $this->viewTicket);
        $this->formValues['tasks'][] = [];
    }

    #[LiveAction]
    public function removeTask(#[LiveArg] int $index): void
    {
        $this->denyAccessUnlessGranted(TicketVoter::EDIT, $this->viewTicket);
        unset($this->formValues['tasks'][$index]);
    }


    #[LiveAction]
    public function toggleTask(#[LiveArg] int $taskId): void
    {
        $this->denyAccessUnlessGranted(TicketVoter::EDIT, $this->viewTicket);
        if (!($task = $this->taskRepository->findByIdAndTicket($taskId, $this->viewTicket))) {
            return;
        }

        $task->toggleFinished();
        $this->em->flush();
        // NOTE: Need re-fetch, else data will be diplayed from already dehydrated state
        $this->resetForm();
    }

    #[LiveListener('tag:created')]
    public function onTagCreated(#[LiveArg] int $tag): void
    {
        if ($this->viewTicket->getId()) {
            $this->denyAccessUnlessGranted(TicketVoter::EDIT, $this->viewTicket);
        }

        if (!($tag = $this->tagRepository->find($tag))) {
            return;
        }

        $this->viewTicket->addTag($tag);

        $currentTags = $this->formValues['tags'] ?? [];
        if (!in_array($tag, $currentTags)) {
            $this->formValues['tags'][] = $tag;
        }

        $this->resetForm();
    }
}
