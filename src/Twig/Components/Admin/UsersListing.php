<?php declare(strict_types=1);

namespace App\Twig\Components\Admin;

use App\Config\UserStatus;
use App\Entity\User;
use App\Repository\UserRepository;
use App\Service\SearchableRepositoryProvider;
use App\Service\User\RegistrationNotifier;
use App\Twig\Components\Common\Listing;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\UX\LiveComponent\Attribute\AsLiveComponent;
use Symfony\UX\LiveComponent\Attribute\LiveAction;
use Symfony\UX\LiveComponent\Attribute\LiveArg;
use Symfony\UX\LiveComponent\Attribute\LiveListener;

#[AsLiveComponent(template: 'components/Common/Listing.html.twig')]
final class UsersListing extends Listing
{
    public function __construct(
        SearchableRepositoryProvider $searchableRepositoryProvider,
        private readonly UserRepository $userRepository,
        private readonly EntityManagerInterface $em,
        private readonly RegistrationNotifier $registrationNotifier,
    ) {
        parent::__construct($searchableRepositoryProvider);
    }

    #[LiveAction]
    #[LiveListener('user:resend-registration-link')]
    public function userResendRegistrationLink(#[LiveArg] int $user): void
    {
        /** @var ?User */
        $user = $this->userRepository->find($user);
        if (!$user || $user->isActive()) {
            return;
        }

        $this->registrationNotifier->notify($user);
    }

    #[LiveAction]
    #[LiveListener('user:toggle-status')]
    public function userToggleStatus(#[LiveArg] int $user): void
    {
        /** @var User */
        $user = $this->userRepository->find($user);
        if (!$user || !$user->isRegistered()) {
            return;
        }

        $newStatus = $user->isActive() ? UserStatus::DISABLED : UserStatus::ACTIVE;
        $user->setStatus($newStatus);
        $this->em->flush();
    }
}
