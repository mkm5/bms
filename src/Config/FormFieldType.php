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
    case SELECT = 'select';
    case RADIO = 'radio';

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
            self::SELECT => 'Select',
            self::RADIO => 'Radio Group',
        };
    }

    public function hasChoices(): bool
    {
        return in_array($this, [self::SELECT, self::RADIO], true);
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
