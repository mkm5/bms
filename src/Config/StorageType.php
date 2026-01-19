<?php declare(strict_types=1);

namespace App\Config;

enum StorageType: int
{
    case DEFAULT = 0;
    case DOCUMENTS = 1;

    public function storageName(): string
    {
        return match($this) {
            self::DOCUMENTS => 'documents',
            default => 'default'
        };
    }
}
