<?php

/*
 * This file is part of the Symfony package.
 *
 * (c) Fabien Potencier <fabien@symfony.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace App\Repository;

use App\Entity\User;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * This custom Doctrine repository is empty because so far we don't need any custom
 * method to query for application user information. But it's always a good practice
 * to define a custom repository that will be used when the application grows.
 *
 * See https://symfony.com/doc/current/doctrine.html#querying-for-objects-the-repository
 *
 * @author Ryan Weaver <weaverryan@gmail.com>
 * @author Javier Eguiluz <javier.eguiluz@gmail.com>
 */
class UserRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, User::class);
    }

    /**
     * @param int $managerId
     * @return int|mixed|string
     */
    public function getUsersForManager(int $managerId){
        // automatically knows to select Products
        // the "p" is an alias you'll use in the rest of the query
        $qb = $this->createQueryBuilder('u')
            ->where('u.manager = :manager')
            ->setParameter('manager', $managerId)
            ->orderBy('u.fullName', 'ASC');

        $query = $qb->getQuery();

        return $query->execute();
    }

    /**
     * @param int $squadId
     * @return int|mixed|string
     */
    public function getUsersForSquad(int $squadId){
        // automatically knows to select Products
        // the "p" is an alias you'll use in the rest of the query
        $qb = $this->createQueryBuilder('u')
            ->where('u.squad = :squad')
            ->setParameter('squad', $squadId)
            ->orderBy('u.fullName', 'ASC');

        $query = $qb->getQuery();

        return $query->execute();
    }

    /**
     * @param int $managerId
     * @return int|mixed|string
     */
    public function getNextBirthdaysUsers(int $nbResults = 4)
    {
        $result = [];
        $user = [];
        $tmp = 0;
        $conn = $this->getEntityManager()->getConnection();

        $sql = "
            SELECT id,
                full_name,
                birthday,
                strftime('%j', birthday) - strftime('%j', 'now') AS days_remaining
            FROM user
            WHERE days_remaining >= 1
            ORDER BY days_remaining
            LIMIT 0,:nbResults";
        $stmt = $conn->prepare($sql);
        $response = $stmt->executeQuery(['nbResults' => $nbResults*2]);
        $responseArray = $response->fetchAllAssociative();

        foreach($responseArray as $responsesArray){
            $date = new \DateTime($responsesArray["birthday"]);
            $birthday = $date->format("d/m");
            if(!key_exists($birthday,$result)){
                $user = [];
                $tmp += 1;
                if($tmp>4){
                    continue;
                }
                $daysRemaining = $responsesArray["days_remaining"];
            }
            $user[$responsesArray["id"]] = [
                "full_name" => $responsesArray["full_name"],
                "days_remaining" => $daysRemaining
            ];
            $result[$birthday] = $user;
        }
        // returns an array of arrays (i.e. a raw data set)
        return $result;
    }

    public function getMissedBirthays()
    {
        $conn = $this->getEntityManager()->getConnection();
        $sql = "
            SELECT id,
                full_name,
                birthday,
                strftime('%j', birthday) - strftime('%j', 'now') AS days_remaining,
                (1 - strftime('%w', 'now')) AS sunday,
                (1 - strftime('%w', 'now') - 1) AS saturday
            FROM user
            WHERE days_remaining <= sunday 
              AND days_remaining >= saturday 
            ORDER BY days_remaining";
        $stmt = $conn->prepare($sql);

        $result = $stmt->executeQuery();
        return $result->fetchAllAssociative();
    }

    /**
     * @param int $managerId
     * @return int|mixed|string
     */
    public function getUsersForManagerGroupBySquad(int $managerId)
    {
        // automatically knows to select Products
        // the "p" is an alias you'll use in the rest of the query
        $qb = $this->createQueryBuilder('u')
            ->where('u.manager = :manager')
            ->setParameter('manager', $managerId)
            ->orderBy('u.squad', 'ASC')
            ->addOrderBy('u.fullName', 'ASC');

        $query = $qb->getQuery();

        return $query->execute();
    }
}
