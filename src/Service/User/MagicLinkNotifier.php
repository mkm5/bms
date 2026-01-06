<?php declare(strict_types=1);

namespace App\Service\User;

use App\Entity\User;
use Symfony\Bridge\Twig\Mime\TemplatedEmail;
use Symfony\Component\DependencyInjection\Attribute\Autowire;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Security\Http\LoginLink\LoginLinkHandlerInterface;

class MagicLinkNotifier
{
    public function __construct(
        #[Autowire(param: 'app.name')]
        private readonly string $appName,
        #[Autowire(param: 'app.registration_link_lifetime')]
        private readonly int $registrationLinkLifetime,
        private readonly MailerInterface $mailer,
        private readonly LoginLinkHandlerInterface $loginLinkHandler,
    ) {
    }

    public function notify(User $user)
    {
        $loginLinkDetails = $this->loginLinkHandler->createLoginLink(
            $user,
            lifetime: $this->registrationLinkLifetime,
        );

        $email = (new TemplatedEmail())
            ->to($user->getEmail())
            ->subject('Login // ' . $this->appName)
            ->htmlTemplate('emails/magic_link_login.html.twig')
            ->context([
                'login_link' => $loginLinkDetails->getUrl(),
            ])
        ;

        $this->mailer->send($email);
    }
}
