<?php

namespace App\Repository;

use App\Entity\Advert;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Advert>
 *
 * @method Advert|null find($id, $lockMode = null, $lockVersion = null)
 * @method Advert|null findOneBy(array $criteria, array $orderBy = null)
 * @method Advert[]    findAll()
 * @method Advert[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class AdvertRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Advert::class);
    }

    public function save(Advert $entity, bool $flush = false): void
    {
        $this->getEntityManager()->persist($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    public function remove(Advert $entity, bool $flush = false): void
    {
        $this->getEntityManager()->remove($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    /*public function deleteEveryPublishedPublishedXDaysAgo(Int $nbDays) : void
    {
        $dateNow = new \DateTime();
        $removeDate = date('Y-m-d', strtotime('-'.$nbDays.' day', strtotime($dateNow->format('Y-m-d'))));

        $this->createQueryBuilder('a')
            ->delete()
            ->where('a.state = published')
            ->andWhere('a.publishedAt <= :removeDate')
            ->setParameter('removeDate', $removeDate)
            ->getQuery()
            ->getResult()
        ;
    }

    public function deleteEveryRejectedCreatedXDaysAgo(Int $nbDays) : void
    {
        $dateNow = new \DateTime();
        $removeDate = date('Y-m-d', strtotime('-'.$nbDays.' day', strtotime($dateNow->format('Y-m-d'))));

        $this->createQueryBuilder('a')
            ->delete()
            ->where('a.state = rejected')
            ->andWhere('a.createdAt <= :removeDate')
            ->setParameter('removeDate', $removeDate)
            ->getQuery()
            ->getResult()
        ;
    }*/

//    /**
//     * @return Advert[] Returns an array of Advert objects
//     */
//    public function findByExampleField($value): array
//    {
//        return $this->createQueryBuilder('a')
//            ->andWhere('a.exampleField = :val')
//            ->setParameter('val', $value)
//            ->orderBy('a.id', 'ASC')
//            ->setMaxResults(10)
//            ->getQuery()
//            ->getResult()
//        ;
//    }

//    public function findOneBySomeField($value): ?Advert
//    {
//        return $this->createQueryBuilder('a')
//            ->andWhere('a.exampleField = :val')
//            ->setParameter('val', $value)
//            ->getQuery()
//            ->getOneOrNullResult()
//        ;
//    }
}
