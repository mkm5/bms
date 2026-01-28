<?php declare(strict_types=1);

namespace App\Config;

enum UserStatus: int
{
    case PENDING = 0;
    case ACTIVE = 1;
    case DISABLED = 2;

    public function textName(): string
    {
        return match($this) {
            UserStatus::PENDING => 'pending',
            UserStatus::ACTIVE => 'active',
            UserStatus::DISABLED => 'disabled',
            default => '<unknown>',
        };
    }
}
