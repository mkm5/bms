<?php declare(strict_types=1);

namespace App\Twig\Components\User;

use App\Entity\Tag;
use App\Form\TagType;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Form\FormInterface;
use Symfony\UX\LiveComponent\Attribute\AsLiveComponent;
use Symfony\UX\LiveComponent\Attribute\LiveAction;
use Symfony\UX\LiveComponent\Attribute\LiveListener;
use Symfony\UX\LiveComponent\Attribute\LiveProp;
use Symfony\UX\LiveComponent\ComponentToolsTrait;
use Symfony\UX\LiveComponent\ComponentWithFormTrait;
use Symfony\UX\LiveComponent\DefaultActionTrait;

#[AsLiveComponent]
final class TagCreate extends AbstractController
{
    use ComponentToolsTrait;
    use ComponentWithFormTrait;
    use DefaultActionTrait;

    #[LiveProp]
    public string $modalName = 'tag-create';

    public function __construct(
        private readonly EntityManagerInterface $em,
    ) {
    }

    protected function instantiateForm(): FormInterface
    {
        return $this->createForm(TagType::class);
    }

    #[LiveAction]
    public function save(): void
    {
        $this->submitForm();

        /** @var Tag $tag */
        $tag = $this->getForm()->getData();

        $this->em->persist($tag);
        $this->em->flush();

        $this->dispatchBrowserEvent('modal:close', ['id' => $this->modalName]);
        $this->emit('tag:created', ['tag' => $tag->getId()]);
        $this->resetForm();
    }

    #[LiveListener('tag:create')]
    public function onTagCreate(): void
    {
        $this->dispatchBrowserEvent('modal:open', ['id' => $this->modalName]);
        $this->resetForm();
    }
}
