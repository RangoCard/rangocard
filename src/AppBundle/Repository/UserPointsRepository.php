<?php

namespace AppBundle\Repository;

/**
 * UserPointsRepository
 *
 * This class was generated by the Doctrine ORM. Add your own custom
 * repository methods below.
 */
class UserPointsRepository extends \Doctrine\ORM\EntityRepository
{
    public function findPointsByUserAndSale($user, $sale)
    {
        $qb = $this->createQueryBuilder('p')
            ->where('p.user = :user')
            ->andWhere('p.sale = :sale')
            ->setParameters(['user' => $user, 'sale' => $sale]);

        return $qb->getQuery()->getResult();
    }
}
