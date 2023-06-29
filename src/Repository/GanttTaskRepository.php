<?php

namespace App\Repository;

use App\Entity\GanttTask;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * This custom Doctrine repository is empty because so far we don't need any custom
 * method to query for application user information. But it's always a good practice
 * to define a custom repository that will be used when the application grows.
 *
 * See https://symfony.com/doc/current/doctrine.html#querying-for-objects-the-repository
 */
class GanttTaskRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, GanttTask::class);
    }

    /**
     * @param int $targetOrder
     * @return array|array[]
     * @throws \Doctrine\DBAL\Driver\Exception
     * @throws \Doctrine\DBAL\Exception
     */
    public function updateOrder(int $targetOrder){
        $conn = $this->getEntityManager()->getConnection();

        $sql = "UPDATE gantt_task SET sortorder = sortorder + 1 ".
            "WHERE sortorder >= :targetOrder";
        $stmt = $conn->prepare($sql);
        return $stmt->execute(['targetOrder' => $targetOrder]);
    }

    /**
     * @return int|mixed
     * @throws \Doctrine\ORM\NoResultException
     * @throws \Doctrine\ORM\NonUniqueResultException
     */
    public function getMaxOrder(){
        $qb = $this->createQueryBuilder('t');

        $qb->select($qb->expr()->max('t.sortOrder'))
            ->setMaxResults(1);

        $query = $qb->getQuery();
        $maxSortOrder = $query->getSingleResult();

        if($maxSortOrder == null){
            return 0;
        };

        return $maxSortOrder[1];
    }

    /**
     * @param int $epicId
     * @return GanttTask[]|mixed[]|object[]
     */
    public function getTasksFromEpic(int $epicId){
        $epic = $this->find($epicId);

        $result = [];

        $firstLevelTasks = $this->findBy(['parent' => $epic]);
        foreach ($firstLevelTasks as $firstLevelTask){
            $firstLevelTask->setParent(null);

            $secondLevelTasks = $this->findBy(['parent' => $firstLevelTask]);

            if($firstLevelTask->getJiraType() == GanttTask::JIRA_TYPE_US && !empty($secondLevelTasks)){
                $firstLevelTask->setType(GanttTask::TYPE_PROJECT);
            }

            array_push($result, $firstLevelTask);
            $result = array_merge($result, $secondLevelTasks);
        }
        return $result;
    }


    public function calculatePlanFromChildren(GanttTask $taskToUpdate){

        $qb = $this->createQueryBuilder('t')
            ->where('t.parent = :parent')
            ->setParameter('parent', $taskToUpdate)
            ->orderBy('t.startDate', 'ASC');

        $query = $qb->getQuery();
        $allSubTasks = $query->getResult();

        $lastEndDate = null;

        if(!empty($allSubTasks)){
            $isFirst = true;
            /**
             * @var GanttTask $subTask
             */
            foreach ($allSubTasks as $subTask){
                if($isFirst){
                    $taskToUpdate->setStartDate($subTask->getStartDate());
                }
                $endDateSub = $subTask->getEndDate();

                if($isFirst){
                    $lastEndDate = $endDateSub;
                    $isFirst = false;
                } elseif ($endDateSub > $lastEndDate) {
                    $lastEndDate = $endDateSub;
                }
            }
        }

        if($lastEndDate !== null){
            $taskToUpdate->setEndDate($lastEndDate);
        }

        return null;
    }
}
