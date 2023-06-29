<?php

namespace App\Repository;

use App\Entity\O3;
use App\Entity\Office;
use App\Entity\User;
use Doctrine\Persistence\ManagerRegistry;


class O3Repository extends UserDateRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, O3::class);
    }

    public function getO3AfterStartDate(User $currentUser, \DateTime $startDate){

        return $this->createQueryBuilder('o')
            ->where('o.collab = :currentUser')
            ->andWhere('o.startAt >= :startDate')
            ->andWhere('o.collaborator is not null')
            ->setParameter('currentUser', $currentUser)
            ->setParameter('startDate', $startDate)
            ->orderBy('o.startAt', 'ASC')
            ->setMaxResults(5)
            ->getQuery()
            ->getResult();
    }

}