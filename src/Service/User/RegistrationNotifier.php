<?php declare(strict_types=1);

namespace App\Service\User;

use App\Entity\User;
use DateInterval;
use DateTimeImmutable;
use Symfony\Bridge\Twig\Mime\TemplatedEmail;
use Symfony\Component\DependencyInjection\Attribute\Autowire;
use Symfony\Component\HttpFoundation\UriSigner;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Routing\RouterInterface;

class RegistrationNotifier
{
    public function __construct(
        #[Autowire(param: 'app.name')]
        private readonly string $appName,
        #[Autowire(param: 'app.registration_link_lifetime')]
        private readonly int $registrationLinkLifetime,
        private readonly MailerInterface $mailer,
        private readonly RouterInterface $router,
        private readonly UriSigner $signer,
    ) {
    }

    public function notify(User $user)
    {
        $url = $this->router->generate(
            'app_user_register_finalize',
            ['id' => $user->getId()],
            UrlGeneratorInterface::ABSOLUTE_URL,
        );

        $n = sprintf("%d seconds", $this->registrationLinkLifetime);
        $dt = (new DateTimeImmutable())->add(DateInterval::createFromDateString($n));

        $email = (new TemplatedEmail())
            ->to($user->getEmail())
            ->subject('Registration // ' . $this->appName)
            ->htmlTemplate('emails/registration.html.twig')
            ->context([
                'user' => $user,
                'activation_link' => $this->signer->sign($url, $dt),
            ])
        ;

        $this->mailer->send($email);
    }
}
