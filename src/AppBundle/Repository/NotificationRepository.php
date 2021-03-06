<?php

namespace AppBundle\Repository;

/**
 * NotificationRepository
 *
 * This class was generated by the Doctrine ORM. Add your own custom
 * repository methods below.
 */
class NotificationRepository extends \Doctrine\ORM\EntityRepository
{
    public function findAllByUser($user)
    {
        $qb = $this->createQueryBuilder('n')
            ->select('
                n.id as id,
                IDENTITY(n.type) as type,
                n.message as message
            ')
            ->innerJoin('n.users', 'users')
            ->where('users = :user')
            ->setParameter('user', $user)
            ->orderBy('n.createdAt', 'DESC');

        return $qb->getQuery()->getResult();
    }
}
