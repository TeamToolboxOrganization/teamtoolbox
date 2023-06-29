<?php

namespace App\Repository;

use App\Entity\Office;
use App\Entity\User;
use App\Entity\UserDate;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\QueryBuilder;
use Doctrine\Persistence\ManagerRegistry;

/**
 * This custom Doctrine repository is empty because so far we don't need any custom
 * method to query for application user information. But it's always a good practice
 * to define a custom repository that will be used when the application grows.
 *
 * See https://symfony.com/doc/current/doctrine.html#querying-for-objects-the-repository
 */
class UserDateRepository extends ServiceEntityRepository
{

    public function __construct(ManagerRegistry $registry, $targetClass = UserDate::class)
    {
        parent::__construct($registry, $targetClass);
        $this->registry = $registry;
    }

    protected ManagerRegistry $registry;

    /**
     *
     */
    public function truncateDatesForManager(User $manager, ?string $otherWhereClause = null){

        $userManager = $this->registry->getManagerForClass(User::class);

        /**
         *  @var $qb QueryBuilder
         */

        $subQueryBuilder = $userManager->createQueryBuilder('u');

        $subQueryBuilder->from(User::class, 'u');
        $subQueryBuilder->select('u');
        $subQueryBuilder->where('u.manager = :manager');

        $qb = $this->createQueryBuilder('speDate');
        $qb->delete($this->_entityName, 'speDate');
        $qb->where($qb->expr()->in('speDate.collab', $subQueryBuilder->getDQL()));
        if($otherWhereClause){
            $qb->andWhere($otherWhereClause);
        }
        $qb->setParameter('manager', $manager);

        $query = $qb->getQuery();
        return $query->execute();
    }

}
