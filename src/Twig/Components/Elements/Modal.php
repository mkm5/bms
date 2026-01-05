<?php declare(strict_types=1);

namespace App\Twig\Components\Elements;

use Symfony\UX\TwigComponent\Attribute\AsTwigComponent;

#[AsTwigComponent]
class Modal
{
    public string $name;

    public bool $open = false;

    public bool $closeButton = true;

    public bool $closeOnBackdropClick = true;

    public int $zIndex = 1000;
}
