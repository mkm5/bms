<?php declare(strict_types=1);

namespace App\Controller;

use App\Entity\File;
use League\Flysystem\FilesystemAdapter;
use Psr\Container\ContainerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\DependencyInjection\Attribute\AutowireLocator;
use Symfony\Component\HttpFoundation\HeaderUtils;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\ResponseHeaderBag;
use Symfony\Component\HttpFoundation\StreamedResponse;
use Symfony\Component\Routing\Attribute\Route;

use function Symfony\Component\String\u;

class FileController extends AbstractController
{
    public function __construct(
        #[AutowireLocator('app.storage', indexAttribute: 'key')]
        private readonly ContainerInterface $storages,
    ) {
    }

    #[Route('/d/{storageId:file.storage}/{publicId:file.publicId}', name: 'app_file')]
    public function __invoke(File $file, Request $request): Response
    {
        if (!$this->storages->has($file->getStorageName())) {
            throw $this->createNotFoundException('Storage not found');
        }

        /** @var FilesystemAdapter */
        $storage = $this->storages->get($file->getStorageName());

        if (!$storage->fileExists($file->getPublicName())) {
            throw $this->createNotFoundException('File not found');
        }

        $cachedResponse = $this->cacheHeaders(new Response, $file);
        if ($cachedResponse->isNotModified($request)) {
            return $cachedResponse;
        }

        $response = new StreamedResponse(function() use ($storage, $file) {
            $resource = $storage->readStream($file->getPublicName());
            $output = fopen('php://output', 'wb');
            stream_copy_to_stream($resource, $output);
            fclose($resource);
            fclose($output);
        });

        $this->fileHeaders($response, $storage, $file);
        $this->cacheHeaders($response, $file);
        return $response;
    }

    private function fileHeaders(Response $response, FilesystemAdapter $storage, File $file): Response
    {
        if ($file->getMimeType()) {
            $response->headers->set('Content-Type', $file->getMimeType());
        }
        $response->headers->set('Content-Disposition', HeaderUtils::makeDisposition(
            ResponseHeaderBag::DISPOSITION_INLINE,
            $file->getOriginalFileName(),
            $file->getOriginalFileNameAsciiOnly(),
        ));
        $response->headers->set('Content-Length', (string) $storage->fileSize($file->getPublicName())->fileSize());
        return $response;
    }

    private function cacheHeaders(Response $response, File $file): Response
    {
        $response->setPrivate();
        $response->setMaxAge(0);
        $response->headers->addCacheControlDirective('must-revalidate');
        $etag = md5($file->getPublicId() . $file->getUploadedAt()->getTimestamp());
        $response->setEtag($etag);
        $response->setLastModified($file->getUploadedAt());
        return $response;
    }
}
