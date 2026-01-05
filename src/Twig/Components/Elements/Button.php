<?php declare(strict_types=1);

namespace App\Twig\Components\Elements;

use Symfony\UX\TwigComponent\Attribute\AsTwigComponent;

#[AsTwigComponent]
class Button
{
    public ?string $variant = null;

    public ?string $size = null;

    public ?string $width = null;

    public bool $disabled = false;
}
