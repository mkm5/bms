<?php declare(strict_types=1);

namespace App\Config;

use ValueError;

enum FormStatus: int
{
    case DRAFT = 0;
    case LIVE = 1;
    case ARCHIVED = 2;

    public static function fromName(string $name): self
    {
        return match(strtolower($name)) {
            'draft' => self::DRAFT,
            'live' => self::LIVE,
            'archived' => self::ARCHIVED,
            default => throw new ValueError('Unknown FormStatus name'),
        };
    }
}
