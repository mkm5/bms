<?php declare(strict_types=1);

namespace App\Form;

use App\Entity\FormDefinition;
use App\Entity\FormField;
use App\Form\Autocomplete\ProjectAutocompleteField;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CollectionType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Component\Validator\Context\ExecutionContextInterface;

class FormDefinitionType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('name', TextType::class, [
                'label' => 'Form Name',
                'empty_data' => '',
            ])
            ->add('description', TextareaType::class, [
                'label' => 'Description',
                'required' => false,
                'attr' => ['rows' => 3],
            ])
            ->add('project', ProjectAutocompleteField::class, [
                'label' => 'Project',
                'required' => true,
                'multiple' => false,
                'constraints' => [
                    new Assert\Expression(
                        'value === null or not value.isFinished()',
                        'Project must not be finished',
                    ),
                ],
            ])
            ->add('fields', CollectionType::class, [
                'help' => 'At least one field marked as required is required.',
                'required' => true,
                'entry_type' => FormFieldEntryType::class,
                'allow_add' => true,
                'allow_delete' => true,
                'by_reference' => false,
                'error_bubbling' => false,
                'constraints' => [
                    new Assert\Count(min: 1, minMessage: 'At least one field is required'),
                    new Assert\Callback($this->validateAtLeastOneFieldIsRequired(...)),
                    new Assert\Callback($this->validateUniqueFieldLabels(...)),
                ],
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults(['data_class' => FormDefinition::class]);
    }

    private function validateAtLeastOneFieldIsRequired($fields, ExecutionContextInterface $context): void
    {
        /** @var FormField $field */
        foreach ($fields as $field) {
            if ($field->isRequired()) return;
        }

        $context->addViolation('At least one field must be marked as required.');
    }

    private function validateUniqueFieldLabels($fields, ExecutionContextInterface $context): void
    {
        $fieldNames = [];

        /** @var FormField $field */
        foreach ($fields as $field) {
            if (in_array($field->getLabel(), $fieldNames)) {
                $context->addViolation('Field labels must be unique');
                return;
            }

            $fieldNames[] = $field->getLabel();
        }
    }
}
