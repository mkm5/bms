<?php declare(strict_types=1);

namespace App\Form;

use App\Form\Autocomplete\TagAutocompleteField;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints as Assert;

class DocumentType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('name', TextType::class, [
                'label' => 'Name',
                'required' => true,
                'constraints' => [
                    new Assert\NotBlank(message: 'Please provide a document name'),
                ],
            ])
            ->add('description', TextareaType::class, [
                'label' => 'Description',
                'required' => false,
            ])
            ->add('tags', TagAutocompleteField::class, [
                'label' => 'Tags',
                'required' => false,
            ])
            ->add('file', FileType::class, [
                'label' => 'File',
                'required' => true,
                'constraints' => [
                    new Assert\NotBlank(
                        message: 'File is required',
                        groups: ['files'],
                    ),
                    new Assert\File(
                        maxSize: '10M',
                        extensions: ['pdf', 'doc', 'docx', 'xls', 'xlsx', 'jpg', 'jpeg', 'png', 'gif', 'webp'],
                        maxSizeMessage: 'The file is too large. Maximum allowed size is 10 MB.',
                        extensionsMessage: 'Please upload a valid document (PDF, Word, Excel) or image (JPEG, PNG, GIF, WebP).',
                        groups: ['files'],
                    ),
                ],
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'csrf_protection' => true,
            'validation_groups' => ['Default', 'files'],
        ]);
    }
}
