<?php declare(strict_types=1);

namespace App\Tests\ValueResolver;

use App\Config\FormStatus;
use App\ValueResolver\FormStatusFromNameResolver;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\ControllerMetadata\ArgumentMetadata;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

final class FormStatusFromNameResolverTest extends TestCase
{
    private FormStatusFromNameResolver $resolver;
    private ArgumentMetadata $metadata;

    protected function setUp(): void
    {
        $this->resolver = new FormStatusFromNameResolver();
        $this->metadata = new ArgumentMetadata('status', FormStatus::class, false, false, null);
    }

    public function testResolvesValidStatus(): void
    {
        $request = new Request(attributes: ['status' => 'live']);
        $result = iterator_to_array($this->resolver->resolve($request, $this->metadata));
        $this->assertSame([FormStatus::LIVE], $result);
    }

    public function testThrowsOnInvalidStatus(): void
    {
        $request = new Request(attributes: ['status' => 'nonexistent']);
        $this->expectException(NotFoundHttpException::class);
        iterator_to_array($this->resolver->resolve($request, $this->metadata));
    }
}
