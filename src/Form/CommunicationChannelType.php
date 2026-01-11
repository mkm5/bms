<?php declare(strict_types=1);

namespace App\Form;

use App\Config\CommunicationType;
use App\Entity\CommunicationChannel;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\EnumType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class CommunicationChannelType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('type', EnumType::class, [
                'class' => CommunicationType::class,
                'label' => 'Type',
                'choice_label' => fn(CommunicationType $type) => match($type) {
                    CommunicationType::EMAIL => 'Email',
                    CommunicationType::PHONE_WORK => 'Work Phone',
                    CommunicationType::PHONE_PERSONAL => 'Personal Phone',
                    CommunicationType::OTHER => 'Other',
                },
            ])
            ->add('value', TextType::class, [
                'label' => 'Value',
                'required' => true,
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => CommunicationChannel::class,
            'empty_data' => fn() => new CommunicationChannel(),
        ]);
    }
}
