<?php declare(strict_types=1);

namespace App\Command;

use App\Entity\TicketStatus;
use App\Repository\TicketStatusRepository;
use App\Repository\UserRepository;
use App\Service\User\UserFactory;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

#[AsCommand(name: 'app:setup', description: 'Setups basic application data')]
class SetupCommand extends Command
{
    public const STATUSES = ['To Do', 'In Progress', 'Paused', 'Done'];

    public const ADMIN_EMAIL = 'admin@localhost';

    public function __construct(
        private readonly UserRepository $userRepository,
        private readonly TicketStatusRepository $ticketStatusRepository,
        private readonly UserFactory $userFactory,
        private readonly EntityManagerInterface $em,
    ) {
        parent::__construct();
    }

    protected function configure(): void
    {
        $this
            ->addOption('skip-ticket-statuses-creation', description: 'Skip creating ticket statuses')
            ->addOption('skip-admin-creation', description: 'Skip creating admin')
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);

        $this->createTicketStatuses($io, (bool) $input->getOption('skip-ticket-statuses-creation'));
        $this->createAdmin($io, (bool) $input->getOption('skip-admin-creation'));

        $this->em->flush();

        return Command::SUCCESS;
    }

    private function createTicketStatuses(SymfonyStyle $io, bool $skip): void
    {
        if ($skip || $this->hasTicketStatuses()) {
            $io->info('Skipping ticket statuses creation.');
            return;
        }

        foreach (self::STATUSES as $idx => $statusName) {
            $io->info(sprintf('Creating "%s" status.', $statusName));
            $ts = TicketStatus::create($statusName, $idx);
            $this->em->persist($ts);
        }
    }

    private function createAdmin(SymfonyStyle $io, bool $skip): void
    {
        if ($skip || $this->hasUsers()) {
            $io->info('Skipping admin creation.');
            return;
        }

        $io->info(sprintf('Creating admin with "%s" email.', self::ADMIN_EMAIL));
        $admin = $this->userFactory->create(self::ADMIN_EMAIL, 'admin', isAdmin: true, isActive: true);
        $this->em->persist($admin);
    }

    private function hasTicketStatuses(): bool
    {
        return $this->ticketStatusRepository->count() > 0;
    }

    private function hasUsers(): bool
    {
        return $this->userRepository->count() > 0;
    }
}
