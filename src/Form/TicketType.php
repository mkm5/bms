<?php declare(strict_types=1);

namespace App\Form;

use App\Entity\Ticket;
use App\Entity\TicketStatus;
use App\Form\Autocomplete\ProjectAutocompleteField;
use App\Form\Autocomplete\TagAutocompleteField;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CollectionType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints as Assert;

class TicketType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('title', TextType::class, [
                'empty_data' => '',
            ])
            ->add('description', TextareaType::class, [
                'empty_data' => '',
                'attr' => ['rows' => 8],
                'required' => false,
            ])
            ->add('project', ProjectAutocompleteField::class, [
                'required' => true,
                'multiple' => false,
                'constraints' => [
                    new Assert\Expression(
                        'value === null or not value.isFinished()',
                        'Project must not be finished',
                    ),
                ],
            ])
            ->add('status', EntityType::class, [
                'class' => TicketStatus::class,
                'choice_label' => 'name',
                // Happens locally, fetches all of the data
                'autocomplete' => true,
            ])
            ->add('tags', TagAutocompleteField::class, [
                'required' => false,
            ])
            ->add('tasks', CollectionType::class, [
                'entry_type' => TicketTaskType::class,
                'allow_add' => true,
                'allow_delete' => true,
                'by_reference' => false,
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults(['data_class' => Ticket::class]);
    }
}
