<?php

namespace App\Repository;

use App\Entity\Mindset;
use App\Entity\MindsetDTO;
use App\Entity\MindsetHistoryDTO;
use App\Entity\User;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;


class MindsetRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Mindset::class);
    }

    /**
     * @param int $managerId
     * @return int|mixed|string
     */
    public function getMindset(int $collabId, int $managerId = null){
        // automatically knows to select Products
        // the "p" is an alias you'll use in the rest of the query
        $qb = $this->createQueryBuilder('n')
            ->andWhere('n.collab = :collab')
            ->andWhere('n.value IS NOT NULL')
            ->setParameter('collab', $collabId)
            ->setMaxResults(2)
            ->orderBy('n.date', 'DESC');

        if($managerId != null){
            $qb->andWhere('n.author = :manager')
                ->setParameter('manager', $managerId);
        }

        $query = $qb->getQuery();
        $lastTwoNotes = $query->execute();

        if(empty($lastTwoNotes)){
            return new MindsetDTO(0, 0);
        }

        $previousMindset = 0;
        $lastMindset = $lastTwoNotes[0]->getValue();

        if (sizeof($lastTwoNotes) == 1){
            return new MindsetDTO(0, $lastMindset);
        }

        if (sizeof($lastTwoNotes) == 2){
            $previousMindset = $lastTwoNotes[1]->getValue();
            $tendance = $lastMindset - $previousMindset;
            return new MindsetDTO($tendance, $lastMindset);
        }

        return new MindsetDTO(0, 0);
    }


    /**
     * @param int $managerId
     * @return int|mixed|string
     */
    public function getMindsetSquad(int $squadId, int $managerId = null){

        $mindsetSquadByMonth = $this->getMindsetHistorySquad($squadId, $managerId);

        if(!empty($mindsetSquadByMonth)){

            $lastMindset = $mindsetSquadByMonth[sizeof($mindsetSquadByMonth)-1]->getValue();

            if (sizeof($mindsetSquadByMonth) == 1){
                return new MindsetDTO(0, $lastMindset);
            }

            if (sizeof($mindsetSquadByMonth) >= 2){
                $previousMindset = $mindsetSquadByMonth[sizeof($mindsetSquadByMonth)-2]->getValue();
                $tendance = $lastMindset - $previousMindset;
                return new MindsetDTO($tendance, $lastMindset);
            }
        }

        return new MindsetDTO(0, 0);
    }

    /**
     * @param int $managerId
     * @return int|mixed|string
     */
    public function getMindsetGlobal(){

        $mindsetSquadByMonth = $this->getMindsetHistoryGlobal();

        if(!empty($mindsetSquadByMonth)){

            $lastMindset = $mindsetSquadByMonth[sizeof($mindsetSquadByMonth)-1]->getValue();

            if (sizeof($mindsetSquadByMonth) == 1){
                return new MindsetDTO(0, $lastMindset);
            }

            if (sizeof($mindsetSquadByMonth) >= 2){
                $previousMindset = $mindsetSquadByMonth[sizeof($mindsetSquadByMonth)-2]->getValue();
                $tendance = $lastMindset - $previousMindset;
                return new MindsetDTO($tendance, $lastMindset);
            }
        }

        return new MindsetDTO(0, 0);
    }

    /**
     * @param int $managerId
     * @return int|mixed|string
     */
    public function getMindsetHistory(int $collabId, int $managerId = null){
        // automatically knows to select Products
        // the "p" is an alias you'll use in the rest of the query
        $qb = $this->createQueryBuilder('n')
            ->andWhere('n.collab = :collab')
            ->andWhere('n.value IS NOT NULL')
            ->setParameter('collab', $collabId)
            ->orderBy('n.date', 'ASC');

        if($managerId != null){
            $qb->andWhere('n.author = :manager')
                ->setParameter('manager', $managerId);
        }

        $query = $qb->getQuery();
        $mindsetHistory = $query->execute();

        $result = [];
        /**
         * @var $mindset Mindset
         */
        foreach ($mindsetHistory as $mindset){
            $result[] = new MindsetHistoryDTO($mindset->getDate(), $mindset->getValue());
        }

        return $result;
    }

    /**
     * @param int $managerId
     * @return int|mixed|string
     */
    public function getMindsetHistoryGlobal(){
        $qb = $this->createQueryBuilder('n')
            ->where('n.value IS NOT NULL')
            ->orderBy('n.date', 'ASC');

        $query = $qb->getQuery();
        $mindsetHistory = $query->execute();

        return $this->getMindsetByMonth($mindsetHistory);
    }


    /**
     * @param int $managerId
     * @return int|mixed|string
     */
    public function getMindsetHistorySquad(int $squadId, int $managerId = null){
        // automatically knows to select Products
        // the "p" is an alias you'll use in the rest of the query
        $qb = $this->createQueryBuilder('n')
            ->from(User::class, 'u')
            ->where('n.collab = u.id')
            ->andWhere('u.squad = :squad')
            ->andWhere('n.value IS NOT NULL')
            ->setParameter('squad', $squadId)
            ->orderBy('n.date', 'ASC');

        if($managerId != null){
            $qb->andWhere('n.author = :manager')
                ->setParameter('manager', $managerId);
        }

        $query = $qb->getQuery();
        $mindsetHistory = $query->execute();

        return $this->getMindsetByMonth($mindsetHistory);
    }

    private function getMindsetByMonth(array $mindsetHistory){
        $result = [];
        $tmpMindSetMonth = [];
        $previousMonth = null;
        /**
         * @var $mindset Mindset
         */
        foreach ($mindsetHistory as $mindset){
            $currentMonth = $mindset->getDate()->format('m/01/Y');
            if($previousMonth == null || $currentMonth == $previousMonth){
                $tmpMindSetMonth[] = $mindset->getValue();
            } else {
                if(!empty($tmpMindSetMonth)){
                    $monthDate = new \DateTime($previousMonth);
                    $monthMindset = array_sum($tmpMindSetMonth)/count($tmpMindSetMonth);
                    $result[] = new MindsetHistoryDTO($monthDate, $monthMindset);
                }
                $tmpMindSetMonth = [];
                $tmpMindSetMonth[] = $mindset->getValue();
            }

            $previousMonth = $currentMonth;
        }

        // Last value
        if(!empty($tmpMindSetMonth)){
            $monthDate = new \DateTime($previousMonth);
            $monthMindset = array_sum($tmpMindSetMonth)/count($tmpMindSetMonth);
            $result[] = new MindsetHistoryDTO($monthDate, $monthMindset);
        }

        return $result;
    }
}
