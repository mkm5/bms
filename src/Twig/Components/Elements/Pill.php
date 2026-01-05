<?php declare(strict_types=1);

namespace App\Twig\Components\Elements;

use Symfony\UX\TwigComponent\Attribute\AsTwigComponent;

#[AsTwigComponent]
class Pill
{
    public string $content;

    public ?string $variant = null;
}
