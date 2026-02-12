<?php declare(strict_types=1);

namespace App\Service;

use App\Config\FormFieldType;
use App\Entity\FormDefinition;
use App\Entity\FormField;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\DateTimeType;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\FormType;
use Symfony\Component\Form\Extension\Core\Type\RangeType;
use Symfony\Component\Form\Extension\Core\Type\TelType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\TimeType;
use Symfony\Component\Form\FormFactoryInterface;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\Validator\Constraints as Assert;
use ValueError;

final readonly class FormDefinitionFormBuilder
{
    private const CHOICE_SELECT_PLACEHOLDER = '-- Select --';

    private const RANGE_MIN = 0;

    private const RANGE_MAX = 100;

    private const RANGE_STEP = 1;

    public function __construct(
        private FormFactoryInterface $formFactory,
    ) {
    }

    public function createForm(FormDefinition $definition, array $data = [], array $options = []): FormInterface
    {
        $builder = $this->formFactory->createBuilder(FormType::class, $data, $options);

        $labels = [];

        foreach ($definition->getFields() as $field) {
            if (array_key_exists($field->getLabel(), $labels)) {
                throw new ValueError('Non unique label found "'.$field->getLabel().'"');
            }

            $labels[$field->getLabel()] = true;

            $fieldType = $this->resolveFieldType($field->getType());
            $fieldOptions = $this->buildFieldOptions($field);

            $builder->add($field->getLabel(), $fieldType, $fieldOptions);
        }

        return $builder->getForm();
    }

    private function resolveFieldType(FormFieldType $type): string
    {
        return match ($type) {
            FormFieldType::TEXT => TextType::class,
            FormFieldType::TEXTAREA => TextareaType::class,
            FormFieldType::EMAIL => EmailType::class,
            FormFieldType::TELEPHONE => TelType::class,
            FormFieldType::DATE => DateType::class,
            FormFieldType::TIME => TimeType::class,
            FormFieldType::DATETIME => DateTimeType::class,
            FormFieldType::RANGE => RangeType::class,
            FormFieldType::CHECKBOX => CheckboxType::class,
            FormFieldType::CHOICE => ChoiceType::class,
        };
    }

    private function buildFieldOptions(FormField $field): array
    {
        $options = [
            'label' => $field->getName(),
            'required' => $field->isRequired(),
            'constraints' => [],
        ];

        if ($field->getHelpText()) {
            $options['help'] = $field->getHelpText();
        }

        if ($field->isRequired()) {
            $options['constraints'][] = new Assert\NotBlank();
        }

        $fieldOptions = $field->getOptions();

        match ($field->getType()) {
            FormFieldType::CHOICE => $this->configureChoiceOptions($options, $fieldOptions),
            FormFieldType::RANGE => $this->configureRangeOptions($options, $fieldOptions),
            FormFieldType::EMAIL => $this->configureEmailOptions($options, $fieldOptions),
            FormFieldType::DATE, FormFieldType::TIME, FormFieldType::DATETIME => $options['widget'] = 'single_text',
            default => null,
        };

        return $options;
    }

    private function configureChoiceOptions(array &$options, array $fieldOptions): void
    {
        $choices = $this->processChoices($fieldOptions['choices'] ?? []);
        $options['choices'] = array_column($choices, 'value', 'label');
        $options['multiple'] = $fieldOptions['multiple'] ?? false;
        $options['expanded'] = $fieldOptions['expanded'] ?? false;

        if (0 === count($choices)) {
            throw new ValueError('Choices cannot be empty');
        }

        $defaults = $fieldOptions['defaults'] ?? [];
        if (!empty($defaults) && !empty($choices)) {
            $defaultValues = [];
            foreach ($defaults as $choiceIndex) {
                if (isset($choices[$choiceIndex])) {
                    $defaultValues[] = $choices[$choiceIndex]['value'];
                }
            }
            if (!empty($defaultValues)) {
                $options['data'] = $options['multiple'] ? $defaultValues : $defaultValues[0];
            }
        }

        if (!$options['expanded']) {
            $options['placeholder'] = self::CHOICE_SELECT_PLACEHOLDER;
        }
    }

    private function configureRangeOptions(array &$options, array $fieldOptions): void
    {
        $options['attr'] = [
            'min' => $fieldOptions['min'] ?? self::RANGE_MIN,
            'max' => $fieldOptions['max'] ?? self::RANGE_MAX,
            'step' => $fieldOptions['step'] ?? self::RANGE_STEP,
        ];
        $options['constraints'][] = new Assert\Range(min: $options['attr']['min'], max: $options['attr']['max']);
    }

    private function configureEmailOptions(array &$options, array $fieldOptions): void
    {
        $options['constraints'][] = new Assert\Email(mode: Assert\Email::VALIDATION_MODE_HTML5_ALLOW_NO_TLD);
    }

    /**
     * @param array{int, array{label: string, value: string|null}} $choices
     * @return array<int, array{label: string, value: string}>
     */
    private function processChoices(array $choices): array
    {
        return array_map($this->processChoice(...), $choices);
    }

    /**
     * @param array{label: string, value: string|null} $choice
     * @return array{label: string, value: string|null}
     */
    private function processChoice(array $choice): array
    {
        return [
            'label' => $choice['label'],
            'value' => empty($choice['value']) && $choice['value'] !== '0'
                ? $choice['label'] : $choice['value']
        ];
    }
}
