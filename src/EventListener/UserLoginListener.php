<?php declare(strict_types=1);

namespace App\EventListener;

use App\Entity\User;
use DateTimeImmutable;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\EventDispatcher\Attribute\AsEventListener;
use Symfony\Component\Security\Http\Event\LoginSuccessEvent;

final class UserLoginListener
{
    public function __construct(
        private readonly EntityManagerInterface $em,
    ) {
    }

    #[AsEventListener]
    public function onLoginSuccessEvent(LoginSuccessEvent $event): void
    {
        $user = $event->getUser();
        if (!($user instanceof User)) {
            return;
        }

        $user->setLastLogin(new DateTimeImmutable);
        $this->em->flush();
    }
}
