<?php declare(strict_types=1);

namespace App\Tests\Service;

use App\Repository\SearchableRepository;
use App\Service\SearchableRepositoryProvider;
use Doctrine\Persistence\ManagerRegistry;
use Doctrine\Persistence\ObjectRepository;
use InvalidArgumentException;
use PHPUnit\Framework\TestCase;

final class SearchableRepositoryProviderTest extends TestCase
{
    private const TEST_ENTITY = 'App\Entity\SomeEntity';

    public function testReturnsSearchableRepository(): void
    {
        $interfaces = [ObjectRepository::class, SearchableRepository::class];
        $repository = $this->createStubForIntersectionOfInterfaces($interfaces);
        $managerRegistry = $this->mockManagerRegistry(self::TEST_ENTITY, $repository);
        $provider = new SearchableRepositoryProvider($managerRegistry);
        $result = $provider->getRepository(self::TEST_ENTITY);
        $this->assertSame($repository, $result);
    }

    public function testThrowsWhenRepositoryIsNotSearchable(): void
    {
        $repository = $this->createStub(ObjectRepository::class);
        $managerRegistry = $this->mockManagerRegistry(self::TEST_ENTITY, $repository);
        $provider = new SearchableRepositoryProvider($managerRegistry);
        $this->expectException(InvalidArgumentException::class);
        $provider->getRepository(self::TEST_ENTITY);
    }

    private function mockManagerRegistry(string $entity, $repository): ManagerRegistry
    {
        $managerRegistry = $this->createMock(ManagerRegistry::class);
        $managerRegistry->expects($this->once())->method('getRepository')
            ->with($entity)
            ->willReturn($repository);
        return $managerRegistry;
    }
}
