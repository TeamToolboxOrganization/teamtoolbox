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
    public string $stateOK = "Accepté";
    public string $stateNotOk = "Refusé";
    public string $stateWaiting = "En cours de validation";

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

        $qb->andWhere('vac.state != :state')
            ->setParameter('state', $this->stateNotOk);

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
            ->select('SUM(vac.value) sumVac')
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

    /**
     * @param int $userId
     * @return float|int|mixed|string
     */
    public function getUpcomingVacations(int $userId)
    {
        $qb = $this->createQueryBuilder('vac')
            ->where('vac.collab = :userId')
            ->setParameter('userId', $userId);
        $qb->andWhere('vac.endAt > CURRENT_TIMESTAMP()');

        $query = $qb->getQuery();

        return $query->execute();
    }

    /**
     * @param \DateTime $day
     * @return array
     */
    public function getCurrentVacation(\DateTime $day, string $state = null) : array
    {
        $qb = $this->createQueryBuilder('vac');

        $qb->addSelect('collab');
        $qb->leftJoin('vac.collab', 'collab');

        $qb->where(':debut BETWEEN vac.startAt AND vac.endAt OR :debut_afternoon BETWEEN vac.startAt AND vac.endAt')
            ->setParameter('debut', $day)
            ->setParameter('debut_afternoon', date_modify(new \DateTime($day->format('Y-m-d H:i:s')), '+11hours59minutes'));

        if($state === $this->stateNotOk){
            $qb->andWhere('vac.state != :state')
                ->setParameter('state', $state);
        }
        elseif(is_null($state)){
            $qb->andWhere('vac.state = :state')
                ->setParameter('state', $this->stateOK);
        }

        $query = $qb->getQuery();

        return $query->execute();
    }

    /**
     * @param int $idManager
     * @return array
     */
    public function getVacations(int $idManager, bool $toValidate = false) : array
    {
        $qb = $this->createQueryBuilder('vac');

        $qb->addSelect('collab');
        $qb->leftJoin('vac.collab', 'collab');

        if($toValidate){
            $qb->where('vac.state = :state')
                ->setParameter('state', $this->stateWaiting);
        }

        $qb->andWhere('collab.manager = :idManager')
            ->setParameter('idManager', $idManager);

        $qb->orderBy('vac.startAt', 'DESC');

        $query = $qb->getQuery();

        return $query->execute();
    }

    /**
     * @param int $userId
     * @return array
     * @throws \Exception
     */
    public function getVacationsLeft(int $userId) : array
    {
        $year = new \DateTime('today');
        $vacationsTaken = $this->countRTTForYear($userId, $year->format('Y')) ;
        $vacationsLeft['RTT'] = $vacationsTaken[0]['sumVac'] == null ? 10 : 10 - $vacationsTaken[0]['sumVac'];

        date_modify($year, '-1 year');
        $vacationsTaken = $this->countCPForYear($userId, $year->format('Y'));
        $vacationsLeft['CPF'] = $vacationsTaken[0]['sumVac'] == null ? 25 : 25 - $vacationsTaken[0]['sumVac'];

        return $vacationsLeft;
    }
}
