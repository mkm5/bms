<?php declare(strict_types=1);

namespace App\Twig\Components\User;

use App\Config\FormFieldType;
use App\Entity\FormDefinition;
use App\Entity\FormField;
use App\Form\FormDefinitionType;
use App\Service\FormDefinitionFormBuilder;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\Form\FormView;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\PropertyAccess\PropertyAccess;
use Symfony\Component\PropertyAccess\PropertyAccessorInterface;
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

    private readonly PropertyAccessorInterface $propertyAccessor;

    public function __construct(
        private readonly EntityManagerInterface $em,
        private readonly FormDefinitionFormBuilder $formDefinitionFormBuilder,
    ) {
        $this->propertyAccessor = PropertyAccess
            ::createPropertyAccessorBuilder()
            ->getPropertyAccessor()
        ;
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

    private function defaultNewFieldFormValues(): array
    {
        return [
            'name' => '',
            'helpText' => '',
            'isRequired' => false,
            'type' => FormFieldType::TEXT->value,
        ];
    }

    #[LiveAction]
    public function addField(): void
    {
        $index = count($this->formValues['fields'] ?? []);
        $this->formValues['fields'][] = $this->defaultNewFieldFormValues();
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
    public function updateFieldOptions(#[LiveArg] string $accessPath, #[LiveArg] mixed $value): void
    {
        $this->propertyAccessor->setValue($this->fieldOptions, $accessPath, $value);
    }

    #[LiveAction]
    public function toggleChoiceDefault(#[LiveArg] int $fieldIndex, #[LiveArg] int $choiceIndex): void
    {
        if (!isset($this->fieldOptions[$fieldIndex]['defaults'])) {
            $this->fieldOptions[$fieldIndex]['defaults'] = [];
        }

        $defaults = &$this->fieldOptions[$fieldIndex]['defaults'];
        $key = array_search($choiceIndex, $defaults, true);

        if ($key !== false) {
            unset($defaults[$key]);
            $defaults = array_values($defaults);
        } else {
            $defaults[] = $choiceIndex;
        }
    }

    #[LiveAction]
    public function save(): RedirectResponse
    {
        if ($this->formDefinition->getId() && !$this->formDefinition->canEdit()) {
            throw $this->createAccessDeniedException('This form can no longer be edited.');
        }

        $this->submitForm();

        /** @var FormDefinition */
        $formDefinition = $this->getForm()->getData();

        $displayOrder = 0;
        foreach ($formDefinition->getFields() as $index => $field) {
            $field->setDisplayOrder($displayOrder++);
            $field->setOptions($this->getFieldOptions(
                $field->getType(),
                $this->fieldOptions[$index] ?? []
            ));
        }

        if (!$formDefinition->getId()) {
            $this->em->persist($formDefinition);
        }
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

    private function getFieldOptions(FormFieldType $fieldType, array $options): array
    {
        return match ($fieldType) {
            FormFieldType::CHOICE => [
                'choices' => $options['choices'] ?? [],
                'multiple' => (bool) ($options['multiple'] ?? false),
                'expanded' => (bool) ($options['expanded'] ?? false),
                'defaults' => array_map('intval', $options['defaults'] ?? []),
            ],
            FormFieldType::RANGE => [
                'min' => (int) ($options['min'] ?? 0),
                'max' => (int) ($options['max'] ?? 100),
                'step' => (int) ($options['step'] ?? 1),
            ],
            default => [],
        };
    }

    public function getPreviewForm(): ?FormView
    {
        $fields = $this->formValues['fields'] ?? [];
        if (empty($fields)) {
            return null;
        }

        $previewDefinition = new FormDefinition();
        $previewDefinition->setName($this->formValues['name'] ?? 'Preview');

        foreach ($fields as $index => $fieldData) {
            if (empty($fieldData['name'])) {
                continue;
            }

            $fieldType = FormFieldType::tryFrom($fieldData['type'] ?? 'text') ?? FormFieldType::TEXT;
            $field = FormField::create(
                $fieldData['name'],
                $fieldType,
                $fieldData['helpText'] ?? null,
                (bool) ($fieldData['isRequired'] ?? false),
                $this->getFieldOptions($fieldType, $this->fieldOptions[$index] ?? []),
            );
            $previewDefinition->addField($field);
        }

        if ($previewDefinition->getFields()->isEmpty()) {
            return null;
        }

        return $this->formDefinitionFormBuilder
            ->createForm($previewDefinition)
            ->createView()
        ;
    }
}
