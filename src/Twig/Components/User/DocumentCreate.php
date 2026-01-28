<?php declare(strict_types=1);

namespace App\Twig\Components\User;

use App\Config\StorageType;
use App\DTO\DocumentCreate as DocumentCreateDto;
use App\Entity\Document;
use App\Entity\DocumentVersion;
use App\Entity\File;
use App\Form\DocumentType;
use Doctrine\ORM\EntityManagerInterface;
use League\Flysystem\FilesystemOperator;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Serializer\SerializerInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;
use Symfony\UX\LiveComponent\Attribute\AsLiveComponent;
use Symfony\UX\LiveComponent\Attribute\LiveAction;
use Symfony\UX\LiveComponent\Attribute\LiveListener;
use Symfony\UX\LiveComponent\Attribute\LiveProp;
use Symfony\UX\LiveComponent\ComponentToolsTrait;
use Symfony\UX\LiveComponent\ComponentWithFormTrait;
use Symfony\UX\LiveComponent\DefaultActionTrait;

#[AsLiveComponent]
final class DocumentCreate extends AbstractController
{
    use ComponentToolsTrait;
    use ComponentWithFormTrait;
    use DefaultActionTrait;

    #[LiveProp]
    public string $modalName;

    #[LiveProp(useSerializerForHydration: true)]
    public DocumentCreateDto $viewDocument;

    public function __construct(
        private readonly SerializerInterface $serializer,
        private readonly ValidatorInterface $validator,
        private readonly EntityManagerInterface $em,
        private readonly FilesystemOperator $documentsStorage,
    ) {
    }

    public function mount()
    {
        $this->viewDocument = new DocumentCreateDto();
    }

    protected function instantiateForm(): FormInterface
    {
        return $this->createForm(DocumentType::class, $this->viewDocument);
    }

    #[LiveAction]
    public function save(Request $request): void
    {
        if (!empty($uploadedFile = $request->files->get('document')['file'])) {
            $this->formValues['file'] = $uploadedFile;
        }

        $this->submitForm();

        /** @var DocumentCreateDto */
        $data = $this->getForm()->getData();

        $file = File::create(
            StorageType::DOCUMENTS,
            $data->file->getClientOriginalName(),
            $data->file->getClientOriginalExtension() ?: $data->file->guessExtension(),
            $data->file->getMimeType(),
        );

        $this->documentsStorage->write(
            $file->getPublicName(),
            $uploadedFile->getContent(),
        );

        $document = Document::create($data->name, $data->description);
        DocumentVersion::create($document, $file);

        $this->em->persist($document);
        $this->em->flush();

        $this->dispatchBrowserEvent('modal:close', ['id' => $this->modalName]);
        $this->emit('listing:refresh');
        $this->resetForm();
    }

    #[LiveListener('document:create')]
    public function onDocumentCreate(): void
    {
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
