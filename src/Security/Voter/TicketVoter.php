<?php

namespace App\Security\Voter;

use App\Entity\Ticket;
use App\Entity\User;
use LogicException;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\Voter\Vote;
use Symfony\Component\Security\Core\Authorization\Voter\Voter;
use Symfony\Component\Security\Core\User\UserInterface;

final class TicketVoter extends Voter
{
    public const EDIT = 'TICKET_EDIT';

    protected function supports(string $attribute, mixed $subject): bool
    {
        return in_array($attribute, [self::EDIT]) && $subject instanceof Ticket;
    }

    protected function voteOnAttribute(string $attribute, mixed $subject, TokenInterface $token, ?Vote $vote = null): bool
    {
        $user = $token->getUser();
        if (!$user instanceof UserInterface) {
            return false;
        }

        /** @var Ticket */
        $ticket = $subject;

        return match($attribute) {
            self::EDIT => $this->canEdit($ticket, $user),
            default => throw new LogicException('Should never be reached'),
        };
    }

    private function canEdit(Ticket $ticket, User $user): bool
    {
        if ($ticket->isArchived() || $ticket->getProject()->isFinished()) {
            return false;
        }

        return true;
    }
}
