<?php declare(strict_types=1);

namespace App\Twig\Components\User;

use App\Config\FormFieldType;
use App\Entity\FormDefinition;
use App\Entity\FormField;
use App\Form\FormDefinitionType;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\UX\LiveComponent\Attribute\AsLiveComponent;
use Symfony\UX\LiveComponent\Attribute\LiveAction;
use Symfony\UX\LiveComponent\Attribute\LiveArg;
use Symfony\UX\LiveComponent\Attribute\LiveProp;
use Symfony\UX\LiveComponent\ComponentToolsTrait;
use Symfony\UX\LiveComponent\ComponentWithFormTrait;
use Symfony\UX\LiveComponent\DefaultActionTrait;

#[AsLiveComponent]
final class FormBuilder extends AbstractController
{
    use ComponentToolsTrait;
    use ComponentWithFormTrait;
    use DefaultActionTrait;

    #[LiveProp]
    public ?FormDefinition $formDefinition = null;

    #[LiveProp(writable: true)]
    public array $fieldOptions = [];

    public function __construct(
        private readonly EntityManagerInterface $em,
    ) {
    }

    public function mount(?FormDefinition $formDefinition = null): void
    {
        $this->formDefinition = $formDefinition ?? new FormDefinition();
        $this->loadFieldOptions();
    }

    protected function instantiateForm(): FormInterface
    {
        return $this->createForm(FormDefinitionType::class, $this->formDefinition);
    }

    public function getFieldTypes(): array
    {
        return FormFieldType::cases();
    }

    #[LiveAction]
    public function addField(): void
    {
        $index = count($this->formValues['fields'] ?? []);
        $this->formValues['fields'][] = [
            'name' => '',
            'helpText' => '',
            'isRequired' => false,
            'type' => FormFieldType::TEXT->value,
        ];
        $this->fieldOptions[$index] = [];
    }

    #[LiveAction]
    public function removeField(#[LiveArg] int $index): void
    {
        unset($this->formValues['fields'][$index]);
        unset($this->fieldOptions[$index]);
        $this->formValues['fields'] = array_values($this->formValues['fields'] ?? []);
        $this->fieldOptions = array_values($this->fieldOptions);
    }

    #[LiveAction]
    public function addChoice(#[LiveArg] int $fieldIndex): void
    {
        if (!isset($this->fieldOptions[$fieldIndex]['choices'])) {
            $this->fieldOptions[$fieldIndex]['choices'] = [];
        }
        $this->fieldOptions[$fieldIndex]['choices'][] = ['label' => '', 'value' => ''];
    }

    #[LiveAction]
    public function removeChoice(#[LiveArg] int $fieldIndex, #[LiveArg] int $choiceIndex): void
    {
        unset($this->fieldOptions[$fieldIndex]['choices'][$choiceIndex]);
        $this->fieldOptions[$fieldIndex]['choices'] = array_values(
            $this->fieldOptions[$fieldIndex]['choices'] ?? []
        );
    }

    #[LiveAction]
    public function save(): RedirectResponse
    {
        $this->submitForm();

        /** @var FormDefinition */
        $formDefinition = $this->getForm()->getData();

        $displayOrder = 0;
        foreach ($formDefinition->getFields() as $index => $field) {
            $field->setDisplayOrder($displayOrder++);
            $this->applyFieldOptions($field, $this->fieldOptions[$index] ?? []);
        }

        $this->em->persist($formDefinition);
        $this->em->flush();

        return $this->redirectToRoute('app_user_form_builder_edit', [
            'id' => $formDefinition->getId(),
        ]);
    }

    private function loadFieldOptions(): void
    {
        $this->fieldOptions = [];
        foreach ($this->formDefinition->getFields() as $index => $field) {
            $this->fieldOptions[$index] = $field->getOptions() ?? [];
        }
    }

    private function applyFieldOptions(FormField $field, array $options): void
    {
        $type = $field->getType();

        $finalOptions = match (true) {
            $type === FormFieldType::SELECT,
            $type === FormFieldType::RADIO => [
                'choices' => $options['choices'] ?? [],
                'multiselect' => (bool) ($options['multiselect'] ?? false),
                'preselected' => $options['preselected'] ?? null,
            ],
            $type === FormFieldType::RANGE => [
                'min' => (int) ($options['min'] ?? 0),
                'max' => (int) ($options['max'] ?? 100),
                'step' => (int) ($options['step'] ?? 1),
            ],
            default => null,
        };

        $field->setOptions($finalOptions);
    }
}
