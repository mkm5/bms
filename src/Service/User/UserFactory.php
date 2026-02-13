<?php declare(strict_types=1);

namespace App\Service\User;

use App\Config\UserStatus;
use App\Entity\User;
use LogicException;
use RuntimeException;
use SensitiveParameter;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Validator\ConstraintViolationListInterface;
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
        ?string $position = null,
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

        if (!empty($firstName)) $user->setFirstName($firstName);
        if (!empty($lastName)) $user->setLastName($lastName);
        if (!empty($position)) $user->setPosition($position);

        $user->setRoles($isAdmin ? [User::ADMIN_ROLE, User::DEFAULT_ROLE] : [User::DEFAULT_ROLE]);
        $statusIfNotIsActive = empty($password) ? UserStatus::PENDING : UserStatus::DISABLED;
        $user->setStatus($isActive ? UserStatus::ACTIVE : $statusIfNotIsActive);

        $errors = $this->validator->validate($user);
        if (count($errors) > 0) {
            throw new RuntimeException(join("\n", $this->flattenValidationErrors($errors)));
        }

        return $user;
    }

    private function flattenValidationErrors(ConstraintViolationListInterface $violations): array
    {
        $errors = [];
        /** @var \Symfony\Component\Validator\ConstraintViolationInterface $violation */
        foreach ($violations as $violation) {
            $errors[] = $violation->getPropertyPath() . ' - ' . $violation->getMessage();
        }

        return $errors;
    }
}
