<?php declare(strict_types=1);

namespace App\Twig\Components\Elements;

use Symfony\UX\TwigComponent\Attribute\AsTwigComponent;

#[AsTwigComponent]
class DropdownFilter
{
    public string $name;

    public string $filterId;

    /** @var non-empty-array<array{'value': mixed, 'label': string, 'checked'?: bool}> */
    public array $options;

    /** UX Live Component */
    public ?string $dataModel = null;
}
