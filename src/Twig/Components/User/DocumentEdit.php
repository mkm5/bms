<?php declare(strict_types=1);

namespace App\Twig\Components\User;

use App\Entity\Document;
use App\Form\DocumentEditType;
use App\Repository\DocumentRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Form\FormInterface;
use Symfony\UX\LiveComponent\Attribute\AsLiveComponent;
use Symfony\UX\LiveComponent\Attribute\LiveAction;
use Symfony\UX\LiveComponent\Attribute\LiveArg;
use Symfony\UX\LiveComponent\Attribute\LiveListener;
use Symfony\UX\LiveComponent\Attribute\LiveProp;
use Symfony\UX\LiveComponent\ComponentToolsTrait;
use Symfony\UX\LiveComponent\ComponentWithFormTrait;
use Symfony\UX\LiveComponent\DefaultActionTrait;

#[AsLiveComponent]
final class DocumentEdit extends AbstractController
{
    use ComponentToolsTrait;
    use ComponentWithFormTrait;
    use DefaultActionTrait;

    #[LiveProp]
    public string $modalName;

    #[LiveProp]
    public ?Document $document = null;

    public function __construct(
        private readonly DocumentRepository $documentRepository,
        private readonly EntityManagerInterface $em,
    ) {
    }

    protected function instantiateForm(): FormInterface
    {
        return $this->createForm(DocumentEditType::class, $this->document);
    }

    #[LiveAction]
    public function save(): void
    {
        $this->submitForm();
        $this->em->flush();
        $this->dispatchBrowserEvent('modal:close', ['id' => $this->modalName]);
        $this->emit('document:update', ['document' => $this->document->getId()]);
    }

    #[LiveListener('document:edit')]
    public function onDocumentEdit(#[LiveArg] int $document): void
    {
        $this->document = $this->documentRepository->find($document);

        if (!$this->document) {
            throw new \ValueError('Document with id "' . $document . '" does not exist');
        }

        $this->dispatchBrowserEvent('modal:open', ['id' => $this->modalName]);
        $this->resetForm();
    }
}
