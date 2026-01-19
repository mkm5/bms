<?php declare(strict_types=1);

namespace App\DependencyInjection\Compiler;

use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;

class FlysystemTaggingPass implements CompilerPassInterface
{
    private const TAG_START = 'flysystem.adapter.';

    private const TAG_END = '.storage';

    public function process(ContainerBuilder $container): void
    {
        foreach ($container->getDefinitions() as $id => $definition) {
            if (str_starts_with($id, self::TAG_START) && str_ends_with($id, self::TAG_END)) {
                $name = str_replace([self::TAG_START, self::TAG_END], '', $id);
                $definition->addTag('app.storage', ['key' => $name]);
            }
        }
    }
}
