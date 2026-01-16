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

        foreach ($definition->getFields() as $field) {
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
        ];

        if ($field->getHelpText()) {
            $options['help'] = $field->getHelpText();
        }

        $fieldOptions = $field->getOptions() ?? [];

        match ($field->getType()) {
            FormFieldType::CHOICE => $this->configureChoiceOptions($options, $fieldOptions),
            FormFieldType::RANGE => $this->configureRangeOptions($options, $fieldOptions),
            FormFieldType::DATE, FormFieldType::TIME, FormFieldType::DATETIME => $options['widget'] = 'single_text',
            default => null,
        };

        return $options;
    }

    private function configureChoiceOptions(array &$options, array $fieldOptions): void
    {
        $choices = $fieldOptions['choices'] ?? [];
        $options['choices'] = $this->buildChoices($fieldOptions);
        $options['multiple'] = $fieldOptions['multiple'] ?? false;
        $options['expanded'] = $fieldOptions['expanded'] ?? false;

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
    }

    private function buildChoices(array $fieldOptions): array
    {
        $choices = [];
        foreach ($fieldOptions['choices'] ?? [] as $choice) {
            $choices[$choice['label']] = $choice['value'];
        }
        return $choices;
    }
}
