<?php

namespace App\Repository;

use App\Entity\Advert;
use App\Entity\Category;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\Query\Expr\Join;
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

    public function save(Category $entity, bool $flush = false): void
    {
        $this->getEntityManager()->persist($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    public function remove(Category $entity, bool $flush = false): void
    {
        $this->getEntityManager()->remove($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    public function getCategoriesDeletable(): array
    {
        $query = $this->createQueryBuilder('c')
            ->select('c.id')
            ->join(Advert::class, 'a', Join::WITH, 'c.id = a.category')
            ->groupBy('c.id')
            ->orderBy('c.id', 'ASC')
            ->getQuery()
            ->getResult();

        $result = [];
        for($i = 0 ; $i < count($query) ; $i++)
        {
            $result[$i] = $query[$i]['id'];
        }
        return $result;
    }

    public function getCategoriesWithAdverts(): array
    {
        return $this->createQueryBuilder('c')
            ->join(Advert::class, 'a', Join::WITH, 'c.id = a.category')
            ->groupBy('c.id')
            ->orderBy('c.id', 'ASC')
            ->getQuery()
            ->getResult();
    }

    public function getCategoriesWithPublishedAdverts(): array
    {
        return $this->createQueryBuilder('c')
            ->join(Advert::class, 'a', Join::WITH, 'c.id = a.category')
            ->where('a.state = :state')
            ->setParameter('state', 'published')
            ->groupBy('c.id')
            ->orderBy('c.id', 'ASC')
            ->getQuery()
            ->getResult();
    }

//    /**
//     * @return Category[] Returns an array of Category objects
//     */
//    public function findByExampleField($value): array
//    {
//        return $this->createQueryBuilder('c')
//            ->andWhere('c.exampleField = :val')
//            ->setParameter('val', $value)
//            ->orderBy('c.id', 'ASC')
//            ->setMaxResults(10)
//            ->getQuery()
//            ->getResult()
//        ;
//    }

//    public function findOneBySomeField($value): ?Category
//    {
//        return $this->createQueryBuilder('c')
//            ->andWhere('c.exampleField = :val')
//            ->setParameter('val', $value)
//            ->getQuery()
//            ->getOneOrNullResult()
//        ;
//    }
}
