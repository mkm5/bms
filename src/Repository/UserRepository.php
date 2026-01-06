<?php declare(strict_types=1);

namespace App\Repository;

use App\Entity\User;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\QueryBuilder;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Component\Security\Core\Exception\UnsupportedUserException;
use Symfony\Component\Security\Core\User\PasswordAuthenticatedUserInterface;
use Symfony\Component\Security\Core\User\PasswordUpgraderInterface;

/**
 * @extends ServiceEntityRepository<User>
 */
class UserRepository extends ServiceEntityRepository implements PasswordUpgraderInterface
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, User::class);
    }

    public function upgradePassword(PasswordAuthenticatedUserInterface $user, string $newHashedPassword): void
    {
        if (!$user instanceof User) {
            throw new UnsupportedUserException(sprintf('Instances of "%s" are not supported.', $user::class));
        }

        $user->setPassword($newHashedPassword);
        $this->getEntityManager()->persist($user);
        $this->getEntityManager()->flush();
    }

    public function findOneActiveByEmail(string $email): ?User
    {
        return $this->findOneBy(['email' => $email, 'isActive' => true]);
    }

    /**
     * @return User[]
     */
    public function search(
        ?string $search = null,
        bool $onlyActive = false,
        bool $onlyAdmins = false,
        ?int $limit = null,
        int $offset = 0,
    ): array
    {
        $qb = $this->createQueryBuilder('u');

        $this->applyListingSearchFilters($qb, $search, $onlyActive, $onlyAdmins);

        $qb->orderBy('u.id', 'DESC');

        if ($limit !== null) $qb->setMaxResults($limit);
        if ($offset > 0) $qb->setFirstResult($offset);

        return $qb->getQuery()->getResult();
    }

    public function countSearch(
        ?string $search = null,
        bool $onlyActive = false,
        bool $onlyAdmins = false,
    ): int
    {
        $qb = $this->createQueryBuilder('u')->select('COUNT(u.id)');
        $this->applyListingSearchFilters($qb, $search, $onlyActive, $onlyAdmins);
        return (int) $qb->getQuery()->getSingleScalarResult();
    }

    private function applyListingSearchFilters(
        QueryBuilder $qb,
        ?string $search,
        bool $onlyActive,
        bool $onlyAdmins,
    ): void
    {
        if (!empty($search)) {
            $qb
                ->andWhere(
                    $qb->expr()->orX(
                        $qb->expr()->like('LOWER(u.firstName)', ':search'),
                        $qb->expr()->like('LOWER(u.lastName)', ':search'),
                        $qb->expr()->like('LOWER(u.position)', ':search'),
                        $qb->expr()->like('LOWER(u.email)', ':search')
                    )
                )
                ->setParameter('search', '%' . strtolower($search) . '%')
            ;
        }

        if ($onlyActive) {
            $qb
                ->andWhere('u.isActive = :isActive')
                ->setParameter('isActive', true)
            ;
        }

        if ($onlyAdmins) {
            $qb
                ->andWhere('JSONB_CONTAINS(u.roles, :adminRole) = true')
                ->setParameter('adminRole', '"' . User::ADMIN_ROLE . '"')
            ;
        }
    }
}
