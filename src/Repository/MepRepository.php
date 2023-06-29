<?php

namespace App\Repository;

use App\Entity\Mep;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\DBAL\Result;
use Doctrine\Persistence\ManagerRegistry;


class MepRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Mep::class);
    }

    /**
     * @param int $userId
     * @param \DateTime|null $startDate
     * @param \DateTime|null $endDate
     * @return int|mixed|string
     */
    public function getMepBetweenDate(?\DateTime $debut, ?\DateTime $fin, int $userId = null)
    {
        $qb = $this->createQueryBuilder('ud')
            ->where('ud.startAt BETWEEN :debut AND :fin OR ud.endAt BETWEEN :debut AND :fin')
            ->setParameter('debut', $debut)
            ->setParameter('fin', $fin);

        $qb->andWhere('ud.state != :canceledState')
            ->setParameter('canceledState', Mep::STATE_CANCELED);

        if($userId != null){
            $qb->andWhere('ud.collab = :userId')
                ->setParameter('userId', $userId);
        }

        $query = $qb->getQuery();

        return $query->execute();
    }

    public function getNextMepUser(int $userId, int $nbResults = 5)
    {
        $conn = $this->getEntityManager()->getConnection();

        $sql = "
        SELECT user_date.id,
               start_at,
               mep.state 'state',
               strftime('%j', start_at) - strftime('%j', 'now') AS days_remaining
        FROM user_date
        LEFT OUTER JOIN mep ON mep.id = user_date.id
        WHERE days_remaining >= 0 and collab_id = :userId and type = 'mep' and state != '" . Mep::STATE_CANCELED . "'
        ORDER BY days_remaining
        LIMIT 0,:nbResults
            ";

        $stmt = $conn->prepare($sql);

        $result = $stmt->executeQuery(['nbResults' => $nbResults, 'userId' => $userId]);

        // returns an array of arrays (i.e. a raw data set)
        return $result->fetchAllAssociative();
    }

    public function getNextMeps(int $nbResults = 4)
    {
        $conn = $this->getEntityManager()->getConnection();

        $sql = "
        SELECT ud.start_at 'start_at',
               ud.id 'id',
               user.full_name 'full_name',
               mep.state 'state',
               strftime('%j', start_at) - strftime('%j', 'now') AS days_remaining,
               strftime('%Y', start_at) - strftime('%Y', 'now') AS years_remaining
        FROM user_date ud
                 LEFT OUTER JOIN mep ON mep.id = ud.id
                 LEFT OUTER JOIN user ON user.id = ud.collab_id
        WHERE days_remaining >= 0 and type = 'mep' and state != '" . Mep::STATE_CANCELED . "' and years_remaining = 0
        ORDER BY days_remaining
        LIMIT 0,:nbResults
            ";
        $stmt = $conn->prepare($sql);

        $result = $stmt->executeQuery(['nbResults' => $nbResults]);

        // returns an array of arrays (i.e. a raw data set)
        return $result->fetchAllAssociative();
    }
}
