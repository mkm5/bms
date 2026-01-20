<?php declare(strict_types=1);

namespace App\Twig\Components\User;

use App\Config\StorageType;
use App\Entity\Document;
use App\Entity\DocumentVersion;
use App\Entity\File;
use App\Form\DocumentVersionType;
use App\Repository\DocumentRepository;
use Doctrine\ORM\EntityManagerInterface;
use League\Flysystem\FilesystemOperator;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\HttpFoundation\Request;
use Symfony\UX\LiveComponent\Attribute\AsLiveComponent;
use Symfony\UX\LiveComponent\Attribute\LiveAction;
use Symfony\UX\LiveComponent\Attribute\LiveArg;
use Symfony\UX\LiveComponent\Attribute\LiveListener;
use Symfony\UX\LiveComponent\Attribute\LiveProp;
use Symfony\UX\LiveComponent\ComponentToolsTrait;
use Symfony\UX\LiveComponent\ComponentWithFormTrait;
use Symfony\UX\LiveComponent\DefaultActionTrait;

#[AsLiveComponent]
final class DocumentVersionUpload extends AbstractController
{
    use ComponentToolsTrait;
    use ComponentWithFormTrait;
    use DefaultActionTrait;

    #[LiveProp]
    public string $modalName = 'document-version';

    #[LiveProp]
    public ?Document $document = null;

    public function __construct(
        private readonly DocumentRepository $documentRepository,
        private readonly EntityManagerInterface $em,
        private readonly FilesystemOperator $documentsStorage,
    ) {
    }

    protected function instantiateForm(): FormInterface
    {
        // dd($_FILES, $this->validationGroups());
        return $this->createForm(DocumentVersionType::class, options: [
            'validation_groups' => ['Default', 'files'],
        ]);
    }

    #[LiveAction]
    public function save(Request $request): void
    {
        $uploadedFile = $request->files->get('document_version');
        if ($uploadedFile) {
            $this->formValues['file'] = $uploadedFile['file'];
        }

        $this->submitForm();

        $data = $this->getForm()->getData();

        /** @var UploadedFile $uploadedFile */
        $uploadedFile = $data['file'];

        $file = File::create(
            StorageType::DOCUMENTS,
            $uploadedFile->getClientOriginalName(),
            $uploadedFile->getClientOriginalExtension() ?: $uploadedFile->guessExtension(),
            $uploadedFile->getMimeType(),
        );

        $this->documentsStorage->write(
            $file->getPublicName(),
            $uploadedFile->getContent(),
        );

        DocumentVersion::create($this->document, $file, $data['note'] ?: null);

        $this->em->flush();

        $this->dispatchBrowserEvent('modal:close', ['id' => $this->modalName]);
        $this->emit('document:update', ['document' => $this->document->getId()]);
        $this->resetForm();
    }

    #[LiveListener('document:version:upload')]
    public function onDocumentVersionUpload(#[LiveArg] int $document): void
    {
        $this->document = $this->documentRepository->find($document);

        if (!$this->document) {
            throw new \ValueError('Document with id "' . $document . '" does not exist');
        }

        $this->dispatchBrowserEvent('modal:open', ['id' => $this->modalName]);
        $this->resetForm();
    }

    private function getDataModelValue(): ?string
    {
        /**
         * Note:
         * Working with files is bugged in UX Live Components.
         * More often than not it throws validation error due to
         * issue with keeping set file.
         */
        return 'norender|*';
    }
}
