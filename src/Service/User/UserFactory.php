<?php declare(strict_types=1);

namespace App\Service\User;

use App\Config\UserStatus;
use App\Entity\User;
use LogicException;
use RuntimeException;
use SensitiveParameter;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;

final class UserFactory
{
    public function __construct(
        private readonly UserPasswordHasherInterface $passwordHasher,
        private readonly ValidatorInterface $validator
    ) {
    }

    public function create(
        string $email,
        #[SensitiveParameter]
        ?string $password = null,
        ?string $firstName = null,
        ?string $lastName = null,
        bool $isAdmin = false,
        bool $isActive = false,
    ): User
    {
        if (empty($password) && $isActive) {
            throw new LogicException('User cannot be active without password');
        }

        $user = new User;
        $user->setEmail($email);

        if (!empty($password)) {
            $user->setPassword($this->passwordHasher->hashPassword($user, $password));
        }

        if (!empty($firstName)) {
            $user->setFirstName($firstName);
        }

        if (!empty($lastName)) {
            $user->setLastName($lastName);
        }

        $user->setRoles($isAdmin ? [User::ADMIN_ROLE, User::DEFAULT_ROLE] : [User::DEFAULT_ROLE]);

        $statusIfNotIsActive = empty($password) ? UserStatus::PENDING : UserStatus::DISABLED;
        $user->setStatus($isActive ? UserStatus::ACTIVE : $statusIfNotIsActive);

        $errors = $this->validator->validate($user);
        if (count($errors) > 0) {
            throw new RuntimeException('Validator errors: ' . (string)$errors);
        }

        return $user;
    }
}
