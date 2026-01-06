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

    #[LiveProp(writable: true, url: new UrlMapping(as: 'q'))]
    public ?string $search = null;

    #[LiveProp(writable: true, url: true)]
    public bool $onlyActiveUsers = false;

    #[LiveProp(writable: true, url: true)]
    public bool $onlyAdmins = false;

    #[LiveProp(writable: true, url: true)]
    public int $page = 1;

    private const PER_PAGE = 20;

    private ?int $_totalUsers = null;

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

    /**
     * @return User[]
     */
    public function getUsers(): array
    {
        $offset = ($this->page - 1) * self::PER_PAGE;

        return $this->userRepository->search(
            search: $this->search,
            onlyActive: $this->onlyActiveUsers,
            onlyAdmins: $this->onlyAdmins,
            limit: self::PER_PAGE,
            offset: $offset
        );
    }

    public function getTotalUsers(): int
    {
        if ($this->_totalUsers === null) {
            $this->_totalUsers = $this->userRepository->countSearch(
                search: $this->search,
                onlyActive: $this->onlyActiveUsers,
                onlyAdmins: $this->onlyAdmins,
            );
        }

        return $this->_totalUsers;
    }

    public function getTotalPages(): int
    {
        return (int) ceil($this->getTotalUsers() / self::PER_PAGE);
    }

    public function hasPreviousPage(): bool
    {
        return $this->page > 1;
    }

    public function hasNextPage(): bool
    {
        return $this->page < $this->getTotalPages();
    }

    public function getFirstItemNumber(): int
    {
        if ($this->getTotalUsers() === 0) {
            return 0;
        }

        return ($this->page - 1) * self::PER_PAGE + 1;
    }

    public function getLastItemNumber(): int
    {
        return min($this->page * self::PER_PAGE, $this->getTotalUsers());
    }

    public static function getModalName(): string
    {
        return self::MODAL_NAME;
    }

    /**
     * @return array<int|string>
     */
    public function getPageNumbers(int $maxPages = 5): array
    {
        $totalPages = $this->getTotalPages();
        $currentPage = $this->page;

        if ($totalPages <= 1) {
            return [];
        }

        if ($totalPages <= $maxPages) {
            return range(1, $totalPages);
        }

        // Calculate how many pages we can show in the middle (excluding first and last page)
        $middleSlots = $maxPages - 2;
        $sidePages = (int) floor($middleSlots / 2);

        $rangeStart = max(2, $currentPage - $sidePages);
        $rangeEnd = min($totalPages - 1, $currentPage + $sidePages);

        // Adjusting range for start
        if ($currentPage <= $sidePages + 1) {
            $rangeStart = 2;
            $rangeEnd = min($middleSlots + 1, $totalPages - 1);
        }

        // Adjusting range for end
        if ($currentPage >= $totalPages - $sidePages) {
            $rangeStart = max(2, $totalPages - $middleSlots);
            $rangeEnd = $totalPages - 1;
        }

        $pages = [];

        if ($rangeStart > 2) {
            $pages[] = null;
        }

        for ($i = $rangeStart; $i <= $rangeEnd; $i++) {
            $pages[] = $i;
        }

        if ($rangeEnd < $totalPages - 1) {
            $pages[] = null;
        }

        return [1, ...$pages, $totalPages];
    }
}
