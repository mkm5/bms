<?php declare(strict_types=1);

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
    public const ARCHIVE = 'TICKET_ARCHIVE';

    private const SUPPORTED_ATTRIBUTES = [self::EDIT, self::ARCHIVE];

    protected function supports(string $attribute, mixed $subject): bool
    {
        return in_array($attribute, self::SUPPORTED_ATTRIBUTES) && $subject instanceof Ticket;
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
            self::ARCHIVE => $this->canArchive($ticket, $user),
            default => throw new LogicException('Should never be reached'),
        };
    }

    private function canEdit(Ticket $ticket, User $user): bool
    {
        return $ticket->isEditable();
    }

    private function canArchive(Ticket $ticket, User $user): bool
    {
        return !$ticket->isArchived() || !$ticket->getProject()->isFinished();
    }
}
