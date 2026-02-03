<?php declare(strict_types=1);

namespace App\EventListener;

use App\Config\FormStatus;
use App\Event\ProjectFinishedEvent;
use App\Repository\FormDefinitionRepository;
use Symfony\Component\EventDispatcher\Attribute\AsEventListener;

final class ProjectFinishedListener
{
    public function __construct(
        private readonly FormDefinitionRepository $formDefinitionRepository,
    ) {
    }

    #[AsEventListener]
    public function onProjectFinished(ProjectFinishedEvent $event): void
    {
        $forms = $this->formDefinitionRepository->findNotArchivedByProject($event->project);
        foreach ($forms as $form) {
            $form->setStatus(FormStatus::ARCHIVED);
        }
    }
}
