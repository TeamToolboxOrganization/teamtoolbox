<?php

namespace App\Repository;

use App\Entity\Office;
use App\Entity\User;
use App\Entity\UserDate;
use App\Entity\Vacation;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\QueryBuilder;
use Doctrine\Persistence\ManagerRegistry;


class OfficeRepository extends UserDateRepository
{

    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Office::class);
    }

    /**
     * @param int $userId
     * @param \DateTime|null $startDate
     * @param \DateTime|null $endDate
     * @return int|mixed|string
     */
    public function getOfficeDateBetweenDate(?\DateTime $debut, ?\DateTime $fin, ?int $userId, bool $isHomeOffice):array
    {
        // efbaff
        $qb = $this->createQueryBuilder('ud');

        $qb->where('ud.startAt BETWEEN :debut AND :fin OR ud.endAt BETWEEN :debut AND :fin')
            ->setParameter('debut', $debut)
            ->setParameter('fin', $fin);

        if($userId != null){
            $qb->andWhere('ud.collab = :userId')
                ->setParameter('userId', $userId);
        }

        if($isHomeOffice){
            $qb->andWhere('ud.importFromRhpi = 1');
        } else {
            $qb->andWhere('ud.importFromRhpi = 0');
        }

        $qb->orderBy('ud.startAt');

        $query = $qb->getQuery();

        return $query->execute();
    }

    public function truncateDatesForManager(User $manager, ?string $otherWhereClause = null )
    {
        return parent::truncateDatesForManager($manager, 'speDate.importFromRhpi = 1');
    }


}