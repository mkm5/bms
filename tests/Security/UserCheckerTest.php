<?php declare(strict_types=1);

namespace App\Tests\Security;

use App\Config\UserStatus;
use App\Entity\User;
use App\Exception\UserNotActiveException;
use App\Security\UserChecker;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;
use Psr\Log\NullLogger;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\Security\Core\User\UserInterface;

final class UserCheckerTest extends TestCase
{
    private UserChecker $checker;

    protected function setUp(): void
    {
        $requestStack = new RequestStack();
        $requestStack->push(new Request());
        $this->checker = new UserChecker(new NullLogger(), $requestStack);
    }

    public static function createUser(bool $withPassword, UserStatus $status): User
    {
        $user = (new User)->setEmail('test@example.com');
        if ($withPassword) $user = $user->setPassword('<password>');
        $user->setStatus($status);
        return $user;
    }

    #[DataProvider('userStatusAndErrorExpectationProvider')]
    public function testUserStatusPreAuth(bool $expectsError, User $user): void
    {
        if ($expectsError) $this->expectException(UserNotActiveException::class);
        $this->checker->checkPreAuth($user);
        if (!$expectsError) $this->addToAssertionCount(1);
    }

    public static function userStatusAndErrorExpectationProvider(): iterable
    {
        yield 'user pending' => [true, self::createUser(false, UserStatus::PENDING)];
        yield 'user active' => [false, self::createUser(true, UserStatus::ACTIVE)];
        yield 'user disabled' => [true, self::createUser(true, UserStatus::DISABLED)];
    }

    public function testNonAppUserSkipsPreAuth(): void
    {
        $user = $this->createStub(UserInterface::class);
        $this->checker->checkPreAuth($user);
        $this->addToAssertionCount(1);
    }
}
