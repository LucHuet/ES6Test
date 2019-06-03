<?php

namespace AppBundle\Repository;

use Doctrine\ORM\EntityRepository;

/**
 * RepLogRepository
 *
 * This class was generated by the Doctrine ORM. Add your own custom
 * repository methods below.
 */
class RepLogRepository extends EntityRepository
{
    public function getLeaderboardDetails()
    {
        return $this->createQueryBuilder('rl')
            ->select('IDENTITY(rl.user) as user_id, SUM(rl.totalLitersDrinked) as drinkSum')
            ->groupBy('rl.user')
            ->orderBy('drinkSum', 'DESC')
            ->getQuery()
            ->execute()
        ;
    }
}
