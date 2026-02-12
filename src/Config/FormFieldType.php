<?php declare(strict_types=1);

namespace App\Config;

use Symfony\Contracts\Translation\TranslatableInterface;
use Symfony\Contracts\Translation\TranslatorInterface;

enum FormFieldType: string implements TranslatableInterface
{
    case TEXT = 'text';
    case TEXTAREA = 'textarea';
    case EMAIL = 'email';
    case TELEPHONE = 'telephone';
    case DATE = 'date';
    case TIME = 'time';
    case DATETIME = 'datetime';
    case RANGE = 'range';
    case CHECKBOX = 'checkbox';
    case CHOICE = 'choice';

    public function hasChoices(): bool
    {
        return $this === self::CHOICE;
    }

    public function trans(TranslatorInterface $translator, ?string $locale = null): string
    {
        return $translator->trans($this->name, domain: 'form_field_types', locale: $locale);
    }
}
