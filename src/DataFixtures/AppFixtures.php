<?php declare(strict_types=1);

namespace App\DataFixtures;

use App\Entity\User;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

class AppFixtures extends Fixture
{
    public function __construct(
        private readonly UserPasswordHasherInterface $passwordHasher,
    ) {
    }

    public function load(ObjectManager $manager): void
    {
        $admin = new User();
        $admin->setEmail('admin@localhost');
        $admin->setPassword($this->passwordHasher->hashPassword($admin, 'admin'));
        $admin->addRole(User::DEFAULT_ROLE);
        $admin->addRole(User::ADMIN_ROLE);
        $admin->setIsActive(true);
        $manager->persist($admin);

        $user1 = new User();
        $user1->setEmail('user1@localhost');
        $user1->addRole(User::DEFAULT_ROLE);
        $manager->persist($user1);

        $manager->flush();
    }
}
