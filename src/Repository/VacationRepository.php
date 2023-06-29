<?php

namespace App\Repository;

use App\Entity\Office;
use App\Entity\User;
use App\Entity\UserDate;
use App\Entity\Vacation;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\Query\Expr\Join;
use Doctrine\ORM\QueryBuilder;
use Doctrine\Persistence\ManagerRegistry;

class VacationRepository extends UserDateRepository
{

    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Vacation::class);
    }

    /**
     * @param \DateTime|null $debut
     * @param \DateTime|null $fin
     * @param int|null $userId
     * @return int|mixed|string
     */
    public function getVacationBetweenDate(?\DateTime $debut, ?\DateTime $fin, ?int $userId)
    {
        $qb = $this->createQueryBuilder('vac');

        $qb->where('vac.startAt BETWEEN :debut AND :fin OR vac.endAt BETWEEN :debut AND :fin')
            ->setParameter('debut', $debut)
            ->setParameter('fin', $fin);

        if($userId != null){
            $qb->andWhere('vac.collab = :userId')
                ->setParameter('userId', $userId);
        }

        $query = $qb->getQuery();

        return $query->execute();
    }

    /**
     * @param int $userId
     * @param int $year
     * @return int|mixed|string
     * @throws \Exception
     */
    public function countCPForYear(int $userId, int $year)
    {
        $debut = new \DateTime($year . '-06-01');
        $fin = new \DateTime(($year + 1) . '-05-31');
        return $this->countVacationForYear($userId, 'Congés payés', $debut, $fin);
    }

    /**
     * @param int $userId
     * @param int $year
     * @return int|mixed|string
     * @throws \Exception
     */
    public function countRTTForYear(int $userId, int $year)
    {
        $debut = new \DateTime($year . '-01-01');
        $fin = new \DateTime($year . '-12-31');
        return $this->countVacationForYear($userId, 'RTT', $debut, $fin);
    }

    /**
     * @param int $userId
     * @param string $type
     * @param int $year
     * @return int|mixed|string
     * @throws \Exception
     */
    private function countVacationForYear(int $userId, string $type, ?\DateTime $debut, ?\DateTime $fin)
    {
        $qb = $this->createQueryBuilder('vac')
            ->where('vac.collab = :userId')
            ->setParameter('userId', $userId);
        $qb->andWhere('vac.type = :type')
            ->setParameter('type', $type);
        $qb->andWhere('vac.state != :state')
            ->setParameter('state', "Refusé");
        $qb->andWhere('vac.startAt BETWEEN :debut AND :fin OR vac.endAt BETWEEN :debut AND :fin')
            ->setParameter('debut', $debut)
            ->setParameter('fin', $fin);

        $query = $qb->getQuery();

        return $query->execute();
    }

}
