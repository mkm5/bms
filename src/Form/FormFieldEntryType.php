<?php declare(strict_types=1);

namespace App\Form;

use App\Config\FormFieldType;
use App\Entity\FormField;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\EnumType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class FormFieldEntryType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('name', TextType::class, [
                'label' => 'Name',
                'empty_data' => '',
            ])
            ->add('helpText', TextareaType::class, [
                'label' => 'Help Text',
                'required' => false,
                'attr' => ['rows' => 2],
            ])
            ->add('isRequired', CheckboxType::class, [
                'label' => 'Required',
                'required' => false,
            ])
            ->add('type', EnumType::class, [
                'class' => FormFieldType::class,
                'label' => 'Type',
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults(['data_class' => FormField::class]);
    }
}
