<?php

namespace App\Repository;

use App\Entity\CustomColor;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<CustomColor>
 *
 * @method CustomColor|null find($id, $lockMode = null, $lockVersion = null)
 * @method CustomColor|null findOneBy(array $criteria, array $orderBy = null)
 * @method CustomColor[]    findAll()
 * @method CustomColor[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class CustomColorRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, CustomColor::class);
    }
}
