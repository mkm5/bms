<?php declare(strict_types=1);

namespace App\DTO;

use App\Entity\FormDefinition;

readonly class FormWithStatistics
{
    public function __construct(
        public FormDefinition $form,
        public int $fields,
        public int $submissions,
    ) {
    }

    public function id(): int
    {
        return $this->form->getId();
    }
}
