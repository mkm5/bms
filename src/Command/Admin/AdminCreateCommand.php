<?php declare(strict_types=1);

namespace App\Command\Admin;

use App\Service\User\UserFactory;
use Doctrine\ORM\EntityManagerInterface;
use RuntimeException;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

#[AsCommand(
    name: 'app:admin:create',
    description: 'Creates a new admin user',
)]
class AdminCreateCommand extends Command
{
    public function __construct(
        private readonly UserFactory $userFactory,
        private readonly EntityManagerInterface $em,
    ) {
        parent::__construct();
    }

    protected function configure(): void
    {
        $this
            ->addArgument('email', InputArgument::OPTIONAL)
            ->addArgument('firstName', InputArgument::OPTIONAL)
            ->addArgument('lastName', InputArgument::OPTIONAL)
            ->addArgument('position', InputArgument::OPTIONAL)
            ->addOption('active', null, InputOption::VALUE_NEGATABLE, 'Marks user as active or inactive', true)
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);

        if (empty($email = $this->getOrAsk($io, $input, 'email', 'Email address'))) {
            $io->error('Email is required!');
            return Command::FAILURE;
        }

        $firstName = $this->getOrAsk($io, $input, 'firstName', 'First name');
        $lastName = $this->getOrAsk($io, $input, 'lastName', 'Last name');
        $position = $this->getOrAsk($io, $input, 'position', 'Position');

        $password = $io->askHidden('Password');
        if (empty($password)) {
            $io->error('Password is required!');
            return Command::FAILURE;
        }

        try {
            $user = $this->userFactory->create(
                email: $email,
                password: $password,
                firstName: $firstName,
                lastName: $lastName,
                position: $position,
                isAdmin: true,
                isActive: (bool) $input->getOption('active'),
            );
        } catch (RuntimeException $e) {
            $io->error($e->getMessage());
            return Command::FAILURE;
        }

        $this->em->persist($user);
        $this->em->flush();

        $io->success('User created!');

        return Command::SUCCESS;
    }

    private function getOrAsk(SymfonyStyle $io, InputInterface $input, string $argumentName, string $prompt): mixed
    {
        if (!($value = $input->getArgument($argumentName))) {
            $value = $io->ask($prompt);
        }
        return $value;
    }
}
