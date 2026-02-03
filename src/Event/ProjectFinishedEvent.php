<?php declare(strict_types=1);

namespace App\Event;

use App\Entity\Project;

final class ProjectFinishedEvent
{
    public function __construct(
        public readonly Project $project,
    ) {
    }
}
