<?php

namespace AppBundle\Repository;

use AppBundle\Entity\User;

/**
 * RestaurantRepository
 *
 * This class was generated by the Doctrine ORM. Add your own custom
 * repository methods below.
 */
class RestaurantRepository extends \Doctrine\ORM\EntityRepository
{
    /**
     * Find restaurants
     *
     * @param User $user
     * @return array
     */
    public function findRestaurantListWithSeals(User $user)
    {
        $qb = $this->createQueryBuilder('r')
            ->select(
                'r.id as restaurantId',
                'r.name as restaurantName',
                'r.fantasyName as restaurantFantasyName',
                'r.picSrc as restaurantPicSrc',
                'sl.id as saleId',
                'sl.name as saleName',
                'sl.description as saleDescription',
                'sl.sealLimit as sealLimit'
            )
            ->leftJoin('r.sales', 'sl')
            ->leftJoin('sl.seals', 'sa')
            ->where('sa.user = :user')
            ->setParameter('user', $user)
        ;

        return $qb->getQuery()->getResult();
    }

    public function findRestaurantList(User $user)
    {
        $qb = $this->createQueryBuilder('r')
            ->select(
                'r.id as restaurantId',
                'r.name as restaurantName',
                'r.fantasyName as restaurantFantasyName',
                'r.picSrc as restaurantPicSrc',
                '\'\' as saleId',
                '\'\' as saleName',
                '\'\' as saleDescription',
                '\'\' as sealLimit'
            )
            ->leftJoin('r.seals', 's')
            ->where('s.user = :user')
            ->setParameter('user', $user)
        ;

        return $qb->getQuery()->getResult();
    }

    /**
     * Find restaurants by city
     * @param $city
     * @return array
     */
    public function findRestaurantsByCity($city = null)
    {
        $qb = $this->createQueryBuilder('restaurant');
        if (!is_null($city) && strlen($city) > 0) {
            $qb->where('restaurant.city LIKE :city')->setParameter('city', '%'.$city.'%');
        }
        return $qb->getQuery()->getResult();
    }
}
