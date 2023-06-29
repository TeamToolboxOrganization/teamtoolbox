<?php

namespace App\Repository;

use App\Entity\Desk;
use App\Entity\Project;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;


class ProjectRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Project::class);
    }

    /**
     * @param string $criteria
     * @return float|int|mixed|string
     */
    public function searchProject(?string $criteria)
    {
        if(is_null($criteria)){
            return [];
        }

        $qb = $this->createQueryBuilder('p');

        $qb->where('p.name LIKE :criteria')
            ->setParameter('criteria', '%' . $criteria. '%');

        $qb->orderBy('p.name');

        $query = $qb->getQuery();

        return $query->execute();
    }
}
