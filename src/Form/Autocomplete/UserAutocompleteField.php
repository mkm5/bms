<?php declare(strict_types=1);

namespace App\Form\Autocomplete;

use App\Entity\User;
use Doctrine\ORM\EntityRepository;
use Doctrine\ORM\QueryBuilder;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\UX\Autocomplete\Form\AsEntityAutocompleteField;
use Symfony\UX\Autocomplete\Form\BaseEntityAutocompleteType;

#[AsEntityAutocompleteField]
class UserAutocompleteField extends AbstractType
{
    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'class' => User::class,
            'choice_label' => fn(User $user) => $user->getDisplayName(),
            'filter_query' => $this->filterQuery(...),
        ]);
    }

    public function getParent(): string
    {
        return BaseEntityAutocompleteType::class;
    }

    private function filterQuery(QueryBuilder $qb, string $query, EntityRepository $repo): void
    {
        $qb
            ->andWhere('entity.isActive = :active')
            ->setParameter('active', true)
        ;

        if (!empty($query)) {
            $qb
                ->andWhere(
                    $qb->expr()->orX(
                        $qb->expr()->like('enity.firstName', ':filter'),
                        $qb->expr()->like('enity.lastName', ':filter'),
                        $qb->expr()->like('enity.email', ':filter'),
                    )
                )
                ->setParameter('filter', '%' . $query . '%')
            ;
        }

        $qb->orderBy('entity.id', 'DESC');
        $qb->setMaxResults(10);
    }
}
