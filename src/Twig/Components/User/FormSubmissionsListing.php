<?php declare(strict_types=1);

namespace App\Twig\Components\User;

use App\Entity\FormDefinition;
use App\Twig\Components\Common\Listing;
use Symfony\UX\LiveComponent\Attribute\AsLiveComponent;
use Symfony\UX\LiveComponent\Attribute\LiveProp;

#[AsLiveComponent(template: 'components/Common/Listing.html.twig')]
class FormSubmissionsListing extends Listing
{
    #[LiveProp]
    public FormDefinition $form;

    public function getFields(): array
    {
        return $this->form->getFields()->toArray();
    }
}
