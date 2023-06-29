<?php

namespace App\Repository;

use App\Entity\Category;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Category>
 *
 * @method Category|null find($id, $lockMode = null, $lockVersion = null)
 * @method Category|null findOneBy(array $criteria, array $orderBy = null)
 * @method Category[]    findAll()
 * @method Category[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class CategoryRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Category::class);
    }

    /**
     * @param $key
     * @Return Category
     *
     * return the first entity of the result.
     */
    public function findLike($key) {
        $query = $this->getEntityManager()
            ->createQuery("
                SELECT p FROM App\Entity\Category p
                WHERE p.name LIKE :key "
            );
        $query->setParameter('key', $key . '%');

        if($query->getResult() == null){
            return null;
        }

        return $query->getResult()[0];
    }

}
