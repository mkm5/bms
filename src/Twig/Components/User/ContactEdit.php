<?php declare(strict_types=1);

namespace App\Twig\Components\User;

use App\Entity\Contact;
use App\Form\ContactType;
use App\Repository\ContactRepository;
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
final class ContactEdit extends AbstractController
{
    use ComponentToolsTrait;
    use ComponentWithFormTrait;
    use DefaultActionTrait;

    #[LiveProp]
    public string $modalName;

    #[LiveProp]
    public ?Contact $viewContact = null;

    public function __construct(
        private readonly ContactRepository $contactRepository,
        private readonly EntityManagerInterface $em,
    ) {
    }

    protected function instantiateForm(): FormInterface
    {
        return $this->createForm(ContactType::class, $this->viewContact);
    }

    #[LiveAction]
    public function save(): void
    {
        $this->submitForm();

        /** @var Contact */
        $contact = $this->getForm()->getData();

        $this->em->persist($contact);
        $this->em->flush();

        $this->dispatchBrowserEvent('modal:close', ['id' => $this->modalName]);
        $this->emit('contact:update', ['contact' => $contact->getId()]);
        $this->emit('listing:refresh');
        $this->viewContact = null;
        $this->resetForm();
    }

    #[LiveAction]
    public function addChannel(): void
    {
        $this->formValues['communcationChannels'][] = [];
    }

    #[LiveAction]
    public function removeChannel(#[LiveArg] int $index): void
    {
        unset($this->formValues['communcationChannels'][$index]);
        $this->formValues['communcationChannels'] = array_values($this->formValues['communcationChannels']);
    }

    #[LiveListener('contact:edit')]
    public function onContactEdit(#[LiveArg] ?int $contact = null): void
    {
        $this->viewContact = !$contact
            ? null
            : $this->contactRepository->find($contact)
        ;

        if ($contact && !$this->viewContact) {
            throw new \ValueError('Contact with id "' . ($contact) . '" does not exist');
        }

        $this->dispatchBrowserEvent('modal:open', ['id' => $this->modalName]);
        $this->resetForm();
    }
}
