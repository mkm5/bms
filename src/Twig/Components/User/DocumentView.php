<?php declare(strict_types=1);

namespace App\Twig\Components\User;

use App\Entity\Document;
use App\Entity\DocumentVersion;
use App\Repository\DocumentRepository;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\UX\LiveComponent\Attribute\AsLiveComponent;
use Symfony\UX\LiveComponent\Attribute\LiveAction;
use Symfony\UX\LiveComponent\Attribute\LiveArg;
use Symfony\UX\LiveComponent\Attribute\LiveListener;
use Symfony\UX\LiveComponent\Attribute\LiveProp;
use Symfony\UX\LiveComponent\ComponentToolsTrait;
use Symfony\UX\LiveComponent\DefaultActionTrait;

#[AsLiveComponent]
final class DocumentView
{
    use ComponentToolsTrait;
    use DefaultActionTrait;

    #[LiveProp]
    public Document $document;

    #[LiveProp]
    public string $editModalName = 'document-edit';

    #[LiveProp]
    public string $versionModalName = 'document-version';

    public function __construct(
        private readonly UrlGeneratorInterface $urlGenerator,
        private readonly DocumentRepository $documentRepository,
    ) {
    }

    public function getFileLink(DocumentVersion $version): string
    {
        return $this->urlGenerator->generate('app_file', [
            'storageId' => $version->getFile()->getStorageId(),
            'publicId' => $version->getFile()->getPublicId(),
        ]);
    }

    #[LiveAction]
    public function editDocument(): void
    {
        $this->emit('document:edit', ['document' => $this->document->getId()]);
    }

    #[LiveAction]
    public function uploadVersion(): void
    {
        $this->emit('document:version:upload', ['document' => $this->document->getId()]);
    }

    #[LiveListener('document:update')]
    public function onDocumentUpdate(#[LiveArg] int $document): void
    {
        $this->document = $this->documentRepository->find($document);
    }
}
