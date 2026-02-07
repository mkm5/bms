<?php declare(strict_types=1);

namespace App\Security\Voter;

use App\Entity\Project;
use App\Entity\User;
use LogicException;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\Voter\Vote;
use Symfony\Component\Security\Core\Authorization\Voter\Voter;

final class ProjectVoter extends Voter
{
    public const EDIT = 'PROJECT_EDIT';
    public const FINISH = 'PROJECT_FINISH';
    public const DELETE = 'PROJECT_DELETE';

    private const ACTIONS = [self::EDIT, self::FINISH, self::DELETE];

    protected function supports(string $attribute, mixed $subject): bool
    {
        return in_array($attribute, self::ACTIONS) && $subject instanceof Project;
    }

    protected function voteOnAttribute(string $attribute, mixed $subject, TokenInterface $token, ?Vote $vote = null): bool
    {
        $user = $token->getUser();
        if (!$user instanceof User) {
            return false;
        }

        /** @var Project */
        $project = $subject;

        return match ($attribute) {
            self::EDIT => $user->isAdmin() && !$project->isFinished(),
            self::FINISH => $user->isAdmin(),
            self::DELETE => $user->isAdmin(),
            default => throw new LogicException('Should never be reached'),
        };
    }
}
