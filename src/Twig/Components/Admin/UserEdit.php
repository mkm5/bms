<?php declare(strict_types=1);

namespace App\Twig\Components\Admin;

use App\Entity\User;
use App\Form\UserCreationType;
use App\Repository\UserRepository;
use App\Service\User\RegistrationNotifier;
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
class UserEdit extends AbstractController
{
    use ComponentToolsTrait;
    use ComponentWithFormTrait;
    use DefaultActionTrait;

    #[LiveProp]
    public string $modalName;

    #[LiveProp]
    public ?User $viewUser = null;

    public function __construct(
        private readonly UserRepository $userRepository,
        private readonly EntityManagerInterface $em,
        private readonly RegistrationNotifier $registrationNotifier,
    ) {
    }

    protected function instantiateForm(): FormInterface
    {
        return $this->createForm(UserCreationType::class, $this->viewUser);
    }

    #[LiveAction]
    public function save(): void
    {
        $this->submitForm();

        /** @var User */
        $user = $this->getForm()->getData();
        $isNewUser = $user->getId() === null;

        if ($isNewUser) $this->em->persist($user);
        $this->em->flush();

        if ($isNewUser) $this->registrationNotifier->notify($user);

        $this->viewUser = null;
        $this->dispatchBrowserEvent('modal:close', ['id' => $this->modalName]);
        $this->emit('listing:refresh');
        $this->resetForm();
    }

    #[LiveListener('user:edit')]
    public function onUserEdit(#[LiveArg] ?int $user = null): void
    {
        $this->viewUser = $user ? $this->userRepository->find($user) : null;

        if ($user && !$this->viewUser) {
            throw new \ValueError('User with id "' . ($user) . '" does not exist');
        }

        $this->dispatchBrowserEvent('modal:open', ['id' => $this->modalName]);
        $this->resetForm();
    }
}
