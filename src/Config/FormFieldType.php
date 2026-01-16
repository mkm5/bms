<?php declare(strict_types=1);

namespace App\Config;

enum FormFieldType: string
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

    public function label(): string
    {
        return match ($this) {
            self::TEXT => 'Text',
            self::TEXTAREA => 'Textarea',
            self::EMAIL => 'Email',
            self::TELEPHONE => 'Telephone',
            self::DATE => 'Date',
            self::TIME => 'Time',
            self::DATETIME => 'Date & Time',
            self::RANGE => 'Range',
            self::CHECKBOX => 'Checkbox',
            self::CHOICE => 'Choice',
        };
    }

    public function hasChoices(): bool
    {
        return $this === self::CHOICE;
    }

    public function isSimpleInput(): bool
    {
        return in_array($this, [
            self::TEXT,
            self::EMAIL,
            self::TELEPHONE,
            self::DATE,
            self::TIME,
            self::DATETIME,
        ], true);
    }
}
