<?php declare(strict_types=1);

namespace App\EventListener;

use App\Entity\File;
use Doctrine\Bundle\DoctrineBundle\Attribute\AsEntityListener;
use Doctrine\ORM\Event\PostRemoveEventArgs;
use Doctrine\ORM\Events;
use League\Flysystem\FilesystemOperator;
use Psr\Container\ContainerInterface;
use Psr\Log\LoggerInterface;
use Symfony\Component\DependencyInjection\Attribute\AutowireLocator;

#[AsEntityListener(event: Events::postRemove, method: 'postRemove', entity: File::class)]
class FileDeleteListener
{
    public function __construct(
        #[AutowireLocator('app.storage', indexAttribute: 'key')]
        private readonly ContainerInterface $storages,
        private readonly LoggerInterface $logger,
    ) {
    }

    public function postRemove(File $file, PostRemoveEventArgs $event): void
    {
        if (!$this->storages->has($file->getStorageName())) {
            $this->logger->warning('File storage not found. Cannot remove file.', $this->reportData($file));
            return;
        }

        /** @var FilesystemOperator */
        $storage = $this->storages->get($file->getStorageName());

        if (!$storage->fileExists($file->getPublicName())) {
            $this->logger->warning('File not present.', $this->reportData($file));
            return;
        }

        $storage->delete($file->getPublicName());
    }

    private function reportData(File $file): array
    {
        return [
            'class' => self::class,
            'storage' => $file->getStorageName(),
            'name' => $file->getPublicName(),
        ];
    }
}
