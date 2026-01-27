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
 * @implements SearchableRepository<User>
 */
class UserRepository extends ServiceEntityRepository implements PasswordUpgraderInterface, SearchableRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, User::class);
    }

    public function upgradePassword(PasswordAuthenticatedUserInterface $user, string $newHashedPassword, bool $flush = true): void
    {
        if (!$user instanceof User) {
            throw new UnsupportedUserException(sprintf('Instances of "%s" are not supported.', $user::class));
        }

        $user->setPassword($newHashedPassword);
        if (!$user->getId()) $this->getEntityManager()->persist($user);
        if ($flush) $this->getEntityManager()->flush();
    }

    public function findOneActiveByEmail(string $email): ?User
    {
        return $this->findOneBy(['email' => $email, 'isActive' => true]);
    }

    /** @return User[] */
    public function search(?string $query = null, array $params = [], ?int $limit = null, int $offset = 0): array
    {
        return $this->buildSearchQuery($query, $params, $limit, $offset)
            ->orderBy('u.id', 'DESC')
            ->getQuery()
            ->getResult()
        ;
    }

    public function searchCount(?string $query = null, array $params = []): int
    {
        return (int) $this->buildSearchQuery($query, $params)
            ->select('COUNT(u.id)')
            ->getQuery()
            ->getSingleScalarResult()
        ;
    }

    private function buildSearchQuery(?string $query = null, array $params = [], ?int $limit = null, int $offset = 0): QueryBuilder
    {
        $qb = $this->createQueryBuilder('u')
            ->setMaxResults($limit)
            ->setFirstResult($limit ? $offset : null)
        ;

        if (in_array('onlyActive', $params)) {
            $qb->andWhere('u.isActive = true');
        }

        if (in_array('onlyAdmins', $params)) {
            $qb->andWhere('JSONB_CONTAINS(u.roles, :adminRole) = true')
                ->setParameter('adminRole', '"'.User::ADMIN_ROLE.'"')
            ;
        }

        if (!empty($query)) {
            $qb->andWhere(
                    $qb->expr()->orX(
                        $qb->expr()->like('LOWER(u.firstName)', 'LOWER(:query)'),
                        $qb->expr()->like('LOWER(u.lastName)', 'LOWER(:query)'),
                        $qb->expr()->like('LOWER(u.position)', 'LOWER(:query)'),
                        $qb->expr()->like('LOWER(u.email)', 'LOWER(:query)')
                    )
                )
                ->setParameter('query', '%'.$query.'%')
            ;
        }

        return $qb;
    }
}
