<?php declare(strict_types=1);

namespace App\DTO;

use Symfony\Component\HttpFoundation\File\UploadedFile;

class DocumentCreate
{
    public function __construct(
        public ?string $name = null,
        public ?string $description = null,
        public ?UploadedFile $file = null,
    ) {
    }
}
