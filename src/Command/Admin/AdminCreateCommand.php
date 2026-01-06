<?php declare(strict_types=1);

namespace App\Command\Admin;

use App\Entity\User;
use App\Repository\UserRepository;
use App\Service\User\RegistrationNotifier;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Validator\Constraints\Email;
use Symfony\Component\Validator\Validator\ValidatorInterface;

#[AsCommand(
    name: 'app:admin:create',
    description: 'Creates a new admin user',
)]
class AdminCreateCommand extends Command
{
    public function __construct(
        private readonly UserPasswordHasherInterface $passwordHasher,
        private readonly UserRepository $userRepository,
        private readonly EntityManagerInterface $em,
        private readonly ValidatorInterface $validator,
        private readonly RegistrationNotifier $registrationNotifier,
    ) {
        parent::__construct();
    }

    protected function configure(): void
    {
        $this
            ->addArgument('email', InputArgument::OPTIONAL)
            ->addArgument('firstName', InputArgument::OPTIONAL)
            ->addArgument('lastName', InputArgument::OPTIONAL)
            ->addOption(
                'active',
                null,
                InputOption::VALUE_NEGATABLE | InputOption::VALUE_OPTIONAL,
                'Marking user as inactive and not providing password will send registration email.',
                false,
                [true, false],
            )
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $user = new User();

        $io = new SymfonyStyle($input, $output);

        $email = $this->getOrAsk($io, $input, 'email', 'Email address');

        if (!$this->isValidEmail($email)) {
            $io->error('Invalid email');
            return Command::FAILURE;
        }

        if ($this->userRepository->findOneBy(['email' => $email])) {
            $io->error('User with "%s" email already exists', $email);
            return Command::FAILURE;
        }

        $user->setEmail($email);

        if (!empty($firstName = $this->getOrAsk($io, $input, 'firstName', 'First name'))) {
            $user->setFirstName($firstName);
        }

        if (!empty($lastName = $this->getOrAsk($io, $input, 'lastName', 'Last name'))) {
            $user->setLastName($lastName);
        }

        if (!empty($position = $this->getOrAsk($io, $input, 'position', 'Position'))) {
            $user->setPosition($position);
        }

        $hasPassword = false;
        $password = $io->askHidden('Password');
        if (!empty($password)) {
            $hasPassword = true;
            $user->setPassword($this->passwordHasher->hashPassword($user, $password));
        }

        $active = (bool) $input->getOption('active');
        if ($active) $user->setIsActive(true);

        if ($active && !$hasPassword) {
            $io->error('Marking a user as active requires setting a password.');
            return Command::FAILURE;
        }

        $sendRegistrationEmail = false;
        if (!$active && !$hasPassword) {
            $io->warning('Password not provided, system will send registration email to specified address "'.$email.'"');
            if (!$io->confirm('Ok?', false)) {
                $io->info('Stopping.');
                return Command::FAILURE;
            }

            $sendRegistrationEmail = true;
        }

        $violations = $this->validator->validate($user);
        if (count($violations) > 0) {
            $io->error((string)$violations);
            return Command::FAILURE;
        }

        $this->em->persist($user);
        $this->em->flush();

        if ($sendRegistrationEmail) {
            $this->registrationNotifier->notify($user);
        }

        return Command::SUCCESS;
    }

    private function getOrAsk(SymfonyStyle $io, InputInterface $input, string $argumentName, string $prompt): mixed
    {
        if (!($value = $input->getArgument($argumentName))) {
            $value = $io->ask($prompt);
        }
        return $value;
    }

    private function isValidEmail(string $email): bool
    {
        $constraint = new Email(mode: Email::VALIDATION_MODE_HTML5_ALLOW_NO_TLD);
        $violations = $this->validator->validate($email, $constraint);
        return count($violations) === 0;
    }
}
