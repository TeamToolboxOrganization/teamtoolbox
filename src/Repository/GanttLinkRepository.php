<?php

namespace App\Repository;

use App\Entity\GanttLink;
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
class GanttLinkRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, GanttLink::class);
    }

    public function getLinksFromTasks(array $tasks): array{
        $result = [];

        /**
         * @var $task GanttTask
         */
        foreach ($tasks as $task){
            $tmpLinks = $this->findBy(['source' => $task]);
            foreach ($tmpLinks as $link){
                $result[] = $link;
            }

            $tmpLinks = $this->findBy(['target' => $task]);
            foreach ($tmpLinks as $link){
                $result[] = $link;
            }
        }
        return $result;
    }
}
