<?php declare(strict_types=1);

namespace App\Tests\Service;

use App\Config\FormFieldType;
use App\Entity\FormDefinition;
use App\Entity\FormField;
use App\Service\FormDefinitionFormBuilder;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Form\Extension\Validator\ValidatorExtension;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\Form\Forms;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Component\Validator\Validation;
use ValueError;

final class FormDefinitionFormBuilderTest extends TestCase
{
    private FormDefinitionFormBuilder $builder;

    protected function setUp(): void
    {
        $validator = Validation::createValidator();
        $formFactory = Forms::createFormFactoryBuilder()
            ->addExtension(new ValidatorExtension($validator))
            ->getFormFactory()
        ;
        $this->builder = new FormDefinitionFormBuilder($formFactory);
    }

    public function testEmptyDefinitionCreatesEmptyForm(): void
    {
        $definition = new FormDefinition();
        $form = $this->builder->createForm($definition);
        self::assertInstanceOf(FormInterface::class, $form);
        self::assertCount(0, $form);
    }

    public function testFieldLabelIsUsedAsFormName(): void
    {
        $definition = $this->createDefinitionWithField(FormField::create('My Field', FormFieldType::TEXT));
        $form = $this->builder->createForm($definition);
        self::assertTrue($form->has('my_field'));
    }

    public function testFieldNameIsUsedAsFormLabel(): void
    {
        $definition = $this->createDefinitionWithField(FormField::create('My Field', FormFieldType::TEXT));
        $form = $this->builder->createForm($definition);
        $options = $form->get('my_field')->getConfig()->getOptions();
        self::assertSame('My Field', $options['label']);
    }

    public function testHelpTextIsSet(): void
    {
        $helpText = 'Enter your full name';
        $ff = FormField::create('Name', FormFieldType::TEXT, helpText: $helpText);
        $definition = $this->createDefinitionWithField($ff);
        $form = $this->builder->createForm($definition);
        $options = $form->get('name')->getConfig()->getOptions();
        self::assertSame($helpText, $options['help']);
    }

    public function testHelpTextIsOmittedWhenEmpty(): void
    {
        $definition = $this->createDefinitionWithField(FormField::create('Name', FormFieldType::TEXT));
        $form = $this->builder->createForm($definition);
        $options = $form->get('name')->getConfig()->getOptions();
        self::assertNull($options['help']);
    }

    public function testRequiredFieldHasNotBlankConstraint(): void
    {
        $ff = FormField::create('Name', FormFieldType::TEXT, isRequired: true);
        $definition = $this->createDefinitionWithField($ff);
        $form = $this->builder->createForm($definition);
        $constraints = $form->get('name')->getConfig()->getOptions()['constraints'];
        self::assertContainsOnlyInstancesOf(Assert\NotBlank::class, $constraints);
    }

    public function testOptionalFieldHasNoConstraints(): void
    {
        $ff = FormField::create('Name', FormFieldType::TEXT, isRequired: false);
        $definition = $this->createDefinitionWithField($ff);
        $form = $this->builder->createForm($definition);
        $constraints = $form->get('name')->getConfig()->getOptions()['constraints'];
        self::assertEmpty($constraints);
    }

    public function testRequiredChoice(): void
    {
        $choiceField = FormField::create('Color', FormFieldType::CHOICE, isRequired: true, options: [
            'choices' => [
                ['label' => 'Red', 'value' => 'red'],
                ['label' => 'Blue', 'value' => 'blue'],
            ],
        ]);

        $definition = $this->createDefinitionWithField($choiceField);
        $form = $this->builder->createForm($definition);
        $form->submit(['color' => '']);
        self::assertFalse($form->isValid());

        $definition = $this->createDefinitionWithField($choiceField->setIsRequired(false));
        $form = $this->builder->createForm($definition);
        $form->submit(['color' => '']);
        self::assertTrue($form->isValid());
    }

    public function testChoiceOptions(): void
    {
        $choiceField = FormField::create('Colors', FormFieldType::CHOICE, options: [
            'choices' => [
                ['label' => 'Red', 'value' => 'red'],
                ['label' => 'Blue', 'value' => 'blue'],
            ],
            'multiple' => true,
            'expanded' => true,
        ]);

        $definition = $this->createDefinitionWithField($choiceField);
        $form = $this->builder->createForm($definition);
        $options = $form->get('colors')->getConfig()->getOptions();
        self::assertTrue($options['multiple']);
        self::assertTrue($options['expanded']);
    }

    public function testChoiceDefaultValueSingle(): void
    {
        $definition = $this->createDefinitionWithField(
            FormField::create('Color', FormFieldType::CHOICE, options: [
                'choices' => [
                    ['label' => 'Red', 'value' => 'red'],
                    ['label' => 'Blue', 'value' => 'blue'],
                ],
                'defaults' => [1],
            ])
        );

        $form = $this->builder->createForm($definition);

        $options = $form->get('color')->getConfig()->getOptions();
        self::assertSame('blue', $options['data']);
    }

    public function testChoiceValueFallsBackToLabelWhenEmpty(): void
    {
        $definition = $this->createDefinitionWithField(
            FormField::create('Color', FormFieldType::CHOICE, options: [
                'choices' => [
                    ['label' => 'Red', 'value' => ''],
                    ['label' => 'Blue', 'value' => null],
                ],
            ])
        );

        $form = $this->builder->createForm($definition);

        $options = $form->get('color')->getConfig()->getOptions();
        self::assertSame(['Red' => 'Red', 'Blue' => 'Blue'], $options['choices']);
    }

    public function testChoiceValueZeroIsPreserved(): void
    {
        $definition = $this->createDefinitionWithField(
            FormField::create('Rating', FormFieldType::CHOICE, options: [
                'choices' => [
                    ['label' => 'None', 'value' => '0'],
                    ['label' => 'One', 'value' => '1'],
                ],
            ])
        );

        $form = $this->builder->createForm($definition);

        $options = $form->get('rating')->getConfig()->getOptions();
        self::assertSame(['None' => '0', 'One' => '1'], $options['choices']);
    }

    public function testRangeFieldHasAttributesAndRangeConstraint(): void
    {
        $definition = $this->createDefinitionWithField(
            FormField::create('Score', FormFieldType::RANGE)
        );

        $form = $this->builder->createForm($definition);

        $options = $form->get('score')->getConfig()->getOptions();
        self::assertArrayHasKey('min', $options['attr']);
        self::assertArrayHasKey('max', $options['attr']);
        self::assertArrayHasKey('step', $options['attr']);

        $rangeConstraints = array_filter($options['constraints'], fn($c) => $c instanceof Assert\Range);
        self::assertCount(1, $rangeConstraints);
    }

    public function testRangeFieldWithCustomAttributes(): void
    {
        $options = ['min' => 10, 'max' => 50, 'step' => 5];
        $ff = FormField::create('Score', FormFieldType::RANGE, options: $options);
        $definition = $this->createDefinitionWithField($ff);
        $form = $this->builder->createForm($definition);
        $fieldOptions = $form->get('score')->getConfig()->getOptions();
        self::assertSame($options, $fieldOptions['attr']);
    }

    public function testEmailFieldHasEmailConstraint(): void
    {
        $definition = $this->createDefinitionWithField(FormField::create('Email', FormFieldType::EMAIL));
        $form = $this->builder->createForm($definition);
        $constraints = $form->get('email')->getConfig()->getOptions()['constraints'];
        $emailConstraints = array_filter($constraints, fn($c) => $c instanceof Assert\Email);
        self::assertCount(1, $emailConstraints);
    }

    public function testEmailFieldEmailValidation(): void
    {
        $definition = $this->createDefinitionWithField(FormField::create('Email', FormFieldType::EMAIL));
        $form = $this->builder->createForm($definition);
        $form->submit(['email' => 'user@example.com']);
        self::assertTrue($form->isValid());

        $definition = $this->createDefinitionWithField(FormField::create('Email', FormFieldType::EMAIL));
        $form = $this->builder->createForm($definition);
        $form->submit(['email' => 'not-an-email']);
        self::assertFalse($form->isValid());
    }

    public function testRequiredCheckbox(): void
    {
        $acceptTerms = FormField::create('Accept Terms', FormFieldType::CHECKBOX, isRequired: true);
        $definition = $this->createDefinitionWithField($acceptTerms);
        $form = $this->builder->createForm($definition);
        $form->submit(['accept_terms' => null]);
        self::assertFalse($form->isValid());
    }

    public function testDuplicateLabelsThrowValueError(): void
    {
        $definition = new FormDefinition();
        $definition->addField(FormField::create('Same Name', FormFieldType::TEXT));
        $definition->addField(FormField::create('Same Name', FormFieldType::TEXT));
        $this->expectException(ValueError::class);
        $this->builder->createForm($definition);
    }

    public function testMultipleFieldsAreAllPresent(): void
    {
        $definition = new FormDefinition();
        $definition->addField(FormField::create('First Name', FormFieldType::TEXT));
        $definition->addField(FormField::create('Last Name', FormFieldType::TEXT));
        $definition->addField(FormField::create('Email', FormFieldType::EMAIL));

        $form = $this->builder->createForm($definition);

        self::assertCount(3, $form);
        self::assertTrue($form->has('first_name'));
        self::assertTrue($form->has('last_name'));
        self::assertTrue($form->has('email'));
    }

    public function testInitialDataIsPassedToForm(): void
    {
        $definition = $this->createDefinitionWithField(FormField::create('Name', FormFieldType::TEXT));
        $form = $this->builder->createForm($definition, ['name' => 'John']);
        self::assertSame('John', $form->get('name')->getData());
    }

    private function createDefinitionWithField(FormField $field): FormDefinition
    {
        $definition = new FormDefinition();
        $definition->addField($field);
        return $definition;
    }
}
