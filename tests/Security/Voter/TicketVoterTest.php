<?php declare(strict_types=1);

namespace App\Tests\Security\Voter;

use App\Entity\Project;
use App\Entity\Ticket;
use App\Entity\User;
use App\Security\Voter\TicketVoter;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;

final class TicketVoterTest extends TestCase
{
    private TicketVoter $voter;

    protected function setUp(): void
    {
        $this->voter = new TicketVoter();
    }

    private function token(): TokenInterface
    {
        $user = (new User)->setEmail('user@test.com')->setPassword('<password>');
        $token = $this->createStub(TokenInterface::class);
        $token->method('getUser')->willReturn($user);
        return $token;
    }

    private static function ticket(bool $archived = false, bool $projectFinished = false): Ticket
    {
        $project = new Project();
        if ($projectFinished) $project->setIsFinished(true);

        $ticket = new Ticket();
        $ticket->setProject($project);
        if ($archived) $ticket->setIsArchived(true);

        return $ticket;
    }

    #[DataProvider('permissionsProvider')]
    public function testVote(string $attribute, Ticket $ticket, bool $expected): void
    {
        $result = $this->voter->vote($this->token(), $ticket, [$attribute]);
        $this->assertSame($expected ? 1 : -1, $result);
    }

    public static function permissionsProvider(): iterable
    {
        yield 'can edit active ticket' => [TicketVoter::EDIT, self::ticket(), true];
        yield 'cannot edit archived ticket' => [TicketVoter::EDIT, self::ticket(archived: true), false];
        yield 'cannot edit ticket in finished project' => [TicketVoter::EDIT, self::ticket(projectFinished: true), false];
        yield 'can archive active ticket' => [TicketVoter::ARCHIVE, self::ticket(), true];
        yield 'can unarchive if project not finished' => [TicketVoter::ARCHIVE, self::ticket(archived: true), true];
        yield 'cannot unarchive if project finished' => [TicketVoter::ARCHIVE, self::ticket(archived: true, projectFinished: true), false];
    }

    public function testAbstainsOnUnsupportedAttribute(): void
    {
        $result = $this->voter->vote($this->token(), self::ticket(), ['UNSUPPORTED']);
        $this->assertSame(0, $result);
    }
}
