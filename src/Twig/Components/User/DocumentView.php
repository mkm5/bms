<?php declare(strict_types=1);

namespace App\Twig\Components\User;

use App\Entity\Document;
use App\Entity\DocumentVersion;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\UX\LiveComponent\Attribute\AsLiveComponent;
use Symfony\UX\LiveComponent\Attribute\LiveProp;
use Symfony\UX\LiveComponent\DefaultActionTrait;

#[AsLiveComponent]
final class DocumentView
{
    use DefaultActionTrait;

    #[LiveProp]
    public Document $document;

    public function __construct(
        private readonly UrlGeneratorInterface $urlGenerator,
    ) {
    }

    public function getFileLink(DocumentVersion $version): string
    {
        return $this->urlGenerator->generate('app_file', [
            'storageId' => $version->getFile()->getStorageId(),
            'publicId' => $version->getFile()->getPublicId(),
        ]);
    }
}
