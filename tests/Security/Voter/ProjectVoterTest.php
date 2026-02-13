<?php declare(strict_types=1);

namespace App\Tests\Security\Voter;

use App\Entity\Project;
use App\Entity\User;
use App\Security\Voter\ProjectVoter;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;

final class ProjectVoterTest extends TestCase
{
    private ProjectVoter $voter;

    protected function setUp(): void
    {
        $this->voter = new ProjectVoter();
    }

    private function token(User $user): TokenInterface
    {
        $token = $this->createStub(TokenInterface::class);
        $token->method('getUser')->willReturn($user);
        return $token;
    }

    private static function admin(): User
    {
        $user = (new User)->setEmail('admin@test.com')->setPassword('<password>');
        $user->setRoles([User::ADMIN_ROLE]);
        return $user;
    }

    private static function regularUser(): User
    {
        return (new User)->setEmail('user@test.com')->setPassword('<password>');
    }

    #[DataProvider('permissionsProvider')]
    public function testVote(string $attribute, User $user, bool $finished, bool $expected): void
    {
        $project = new Project();
        if ($finished) $project->setIsFinished(true);

        $result = $this->voter->vote($this->token($user), $project, [$attribute]);
        $this->assertSame($expected ? 1 : -1, $result);
    }

    public static function permissionsProvider(): iterable
    {
        yield 'admin can edit active project' => [ProjectVoter::EDIT, self::admin(), false, true];
        yield 'admin cannot edit finished project' => [ProjectVoter::EDIT, self::admin(), true, false];
        yield 'admin can finish' => [ProjectVoter::FINISH, self::admin(), false, true];
        yield 'admin can delete' => [ProjectVoter::DELETE, self::admin(), false, true];
        yield 'regular user cannot edit' => [ProjectVoter::EDIT, self::regularUser(), false, false];
        yield 'regular user cannot finish' => [ProjectVoter::FINISH, self::regularUser(), false, false];
        yield 'regular user cannot delete' => [ProjectVoter::DELETE, self::regularUser(), false, false];
    }

    public function testAbstainsOnUnsupportedAttribute(): void
    {
        $result = $this->voter->vote($this->token(self::admin()), new Project(), ['UNSUPPORTED']);
        $this->assertSame(0, $result);
    }
}
