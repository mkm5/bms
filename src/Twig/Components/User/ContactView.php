<?php declare(strict_types=1);

namespace App\Twig\Components\User;

use App\Entity\Contact;
use App\Repository\ContactRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\UX\LiveComponent\Attribute\AsLiveComponent;
use Symfony\UX\LiveComponent\Attribute\LiveArg;
use Symfony\UX\LiveComponent\Attribute\LiveListener;
use Symfony\UX\LiveComponent\Attribute\LiveProp;
use Symfony\UX\LiveComponent\ComponentToolsTrait;
use Symfony\UX\LiveComponent\DefaultActionTrait;

#[AsLiveComponent]
final class ContactView
{
    use ComponentToolsTrait;
    use DefaultActionTrait;

    public const MODAL_NAME = 'contact';

    #[LiveProp]
    public Contact $contact;

    public function __construct(
        private readonly ContactRepository $contactRepository,
        private readonly EntityManagerInterface $em,
    ) {
    }

    #[LiveListener('contact:update')]
    public function onContactUpdate(#[LiveArg] ?int $contact = null): void
    {
        if ($contact && $this->contact->getId() === $contact) {
            $this->contact = $this->contactRepository->find($contact);
        }
    }
}
