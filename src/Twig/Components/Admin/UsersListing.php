<?php declare(strict_types=1);

namespace App\Twig\Components\Admin;

use App\Entity\User;
use App\Form\UserCreationType;
use App\Repository\UserRepository;
use App\Service\Paginator;
use App\Service\PaginatorTrait;
use App\Service\User\RegistrationNotifier;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Form\FormInterface;
use Symfony\UX\LiveComponent\Attribute\AsLiveComponent;
use Symfony\UX\LiveComponent\Attribute\LiveAction;
use Symfony\UX\LiveComponent\Attribute\LiveArg;
use Symfony\UX\LiveComponent\Attribute\LiveProp;
use Symfony\UX\LiveComponent\ComponentToolsTrait;
use Symfony\UX\LiveComponent\ComponentWithFormTrait;
use Symfony\UX\LiveComponent\DefaultActionTrait;
use Symfony\UX\LiveComponent\Metadata\UrlMapping;

#[AsLiveComponent]
final class UsersListing extends AbstractController
{
    public const MODAL_NAME = 'user';

    use ComponentToolsTrait;
    use DefaultActionTrait;
    use ComponentWithFormTrait;
    use PaginatorTrait;

    #[LiveProp(writable: true, url: new UrlMapping(as: 'q'))]
    public ?string $search = null;

    #[LiveProp(writable: true, url: true)]
    public bool $onlyActiveUsers = false;

    #[LiveProp(writable: true, url: true)]
    public bool $onlyAdmins = false;

    #[LiveProp(writable: true, url: true)]
    public int $page = 1;

    private const PER_PAGE = 20;

    private ?Paginator $paginator = null;

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
    public function editUser(#[LiveArg] ?int $userId = null): void
    {
        $this->viewUser = $userId
            ? $this->userRepository->find($userId)
            : null
        ;

        $this->resetForm();
        $this->dispatchBrowserEvent('modal:open', ['id' => self::MODAL_NAME]);
    }

    #[LiveAction]
    public function save(): void
    {
        $this->submitForm();

        /** @var User */
        $user = $this->getForm()->getData();
        $isNewUser = $user->getId() === null;

        $this->em->persist($user);
        $this->em->flush();

        if ($isNewUser) {
            $this->registrationNotifier->notify($user);
        }

        $this->viewUser = null;
        $this->resetForm();
        $this->dispatchBrowserEvent('modal:close', ['id' => self::MODAL_NAME]);
    }

    #[LiveAction]
    public function resendRegistrationLink(#[LiveArg] int $userId): void
    {
        /** @var User */
        $user = $this->userRepository->find($userId);

        if ($user === null || $user->isActive()) {
            return;
        }

        $this->registrationNotifier->notify($user);
    }

    #[LiveAction]
    public function toggleUserStatus(#[LiveArg] int $userId): void
    {
        $user = $this->userRepository->find($userId);

        if ($user === null) {
            return;
        }

        $user->setIsActive(!$user->isActive());
        $this->em->flush();
    }

    private function countUsers(): int
    {
        return $this->userRepository->countSearch(
            search: $this->search,
            onlyActive: $this->onlyActiveUsers,
            onlyAdmins: $this->onlyAdmins,
        );
    }

    /**
     * @var Users[]
     */
    private function fetchUsers(int $limit, int $offset): array
    {
        return $this->userRepository->search(
            search: $this->search,
            onlyActive: $this->onlyActiveUsers,
            onlyAdmins: $this->onlyAdmins,
            limit: $limit,
            offset: $offset,
        );
    }

    /**
     * @return Paginator<User>
     */
    protected function getPaginator(): Paginator
    {
        if ($this->paginator === null) {
            $this->paginator = new Paginator(
                currentPage: $this->page,
                itemsPerPage: self::PER_PAGE,
                countCallback: $this->countUsers(...),
                fetchCallback: $this->fetchUsers(...),
            );
        }

        return $this->paginator;
    }

    /**
     * @return User[]
     */
    public function getUsers(): array
    {
        return $this->getItems();
    }

    public function getTotalUsers(): int
    {
        return $this->getTotalItems();
    }

    public static function getModalName(): string
    {
        return self::MODAL_NAME;
    }
}
