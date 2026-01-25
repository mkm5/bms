<?php declare(strict_types=1);

namespace App\DTO;

use App\Entity\FormDefinition;

class FormWithStatistics
{
    public function __construct(
        public readonly FormDefinition $form,
        public readonly int $fields,
        public readonly int $submissions,
    ) {
    }
}
