<?php

namespace App\Repository;

use App\Entity\User;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<User>
 */
class UserRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, User::class);
    }

    /**
     * Retourne le rang de l'utilisateur (classé par XP décroissant).
     * Retourne 0 si l'utilisateur n'a pas encore de compte.
     */
    public function getRankForUser(User $user): int
    {
        $result = $this->createQueryBuilder('u')
            ->select('COUNT(u.id) as rankCount')
            ->where('u.xp > :xp')
            ->setParameter('xp', $user->getXp())
            ->getQuery()
            ->getSingleScalarResult();

        return (int)$result + 1;
    }
}
