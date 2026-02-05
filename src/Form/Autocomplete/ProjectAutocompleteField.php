<?php declare(strict_types=1);

namespace App\Form\Autocomplete;

use App\Entity\Project;
use Doctrine\ORM\EntityRepository;
use Doctrine\ORM\QueryBuilder;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\UX\Autocomplete\Form\AsEntityAutocompleteField;
use Symfony\UX\Autocomplete\Form\BaseEntityAutocompleteType;

#[AsEntityAutocompleteField]
class ProjectAutocompleteField extends AbstractType
{
    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'class' => Project::class,
            'choice_label' => fn(Project $project) => $project->getName(),
            'filter_query' => $this->filterQuery(...),
            'tom_select_options' => [
                'dropdownParent' => 'body',
            ],
        ]);
    }

    public function getParent(): string
    {
        return BaseEntityAutocompleteType::class;
    }

    private function filterQuery(QueryBuilder $qb, string $query, EntityRepository $repo): void
    {
        $qb->andWhere('entity.isFinished = false');

        if (!empty($query)) {
            $qb
                ->andWhere('LOWER(entity.name) LIKE :filter')
                ->setParameter('filter', '%' . strtolower($query) . '%')
            ;
        }

        $qb->orderBy('entity.name', 'ASC');
        $qb->setMaxResults(10);
    }
}
