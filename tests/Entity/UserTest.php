<?php declare(strict_types=1);

namespace App\Tests\Entity;

use App\Config\UserStatus;
use App\Entity\User;
use LogicException;
use PHPUnit\Framework\TestCase;

final class UserTest extends TestCase
{
    public function testNewUserIsPending(): void
    {
        $user = new User();
        $this->assertSame(UserStatus::PENDING, $user->getStatus());
        $this->assertFalse($user->isActive());
        $this->assertFalse($user->isRegistered());
    }

    public function testSetPasswordActivatesPendingUser(): void
    {
        $user = new User();
        $user->setPassword('<password>');
        $this->assertSame(UserStatus::ACTIVE, $user->getStatus());
        $this->assertTrue($user->isActive());
        $this->assertTrue($user->isRegistered());
    }

    public function testSetPasswordDoesNotChangeStatusOfRegisteredUser(): void
    {
        $user = new User();
        $user->setPassword('<password>');
        $user->setStatus(UserStatus::DISABLED);

        $user->setPassword('<new-password>');
        $this->assertSame(UserStatus::DISABLED, $user->getStatus());
    }

    public function testRegisteredUserCanSwitchBetweenActiveAndDisabled(): void
    {
        $user = (new User())->setPassword('<password>');

        $user->setStatus(UserStatus::DISABLED);
        $this->assertFalse($user->isActive());

        $user->setStatus(UserStatus::ACTIVE);
        $this->assertTrue($user->isActive());
    }

    public function testRegisteredUserCannotBeSetToPending(): void
    {
        $this->expectException(LogicException::class);
        (new User)
            ->setPassword('<password>')
            ->setStatus(UserStatus::PENDING)
        ;
    }

    public function testUnregisteredUserCannotBeActivated(): void
    {
        $this->expectException(LogicException::class);
        (new User)->setStatus(UserStatus::ACTIVE);
    }

    public function testUnregisteredUserCannotBeDisabled(): void
    {
        $this->expectException(LogicException::class);
        (new User)->setStatus(UserStatus::DISABLED);
    }
}
