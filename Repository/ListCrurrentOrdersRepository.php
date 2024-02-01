<?php

namespace App\Repository;

use App\Entity\ListCrurrentOrders;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method ListCrurrentOrders|null find($id, $lockMode = null, $lockVersion = null)
 * @method ListCrurrentOrders|null findOneBy(array $criteria, array $orderBy = null)
 * @method ListCrurrentOrders[]    findAll()
 * @method ListCrurrentOrders[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class ListCrurrentOrdersRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, ListCrurrentOrders::class);
    }

    // /**
    //  * @return ListCrurrentOrders[] Returns an array of ListCrurrentOrders objects
    //  */
    /*
    public function findByExampleField($value)
    {
        return $this->createQueryBuilder('l')
            ->andWhere('l.exampleField = :val')
            ->setParameter('val', $value)
            ->orderBy('l.id', 'ASC')
            ->setMaxResults(10)
            ->getQuery()
            ->getResult()
        ;
    }
    */

    /*
    public function findOneBySomeField($value): ?ListCrurrentOrders
    {
        return $this->createQueryBuilder('l')
            ->andWhere('l.exampleField = :val')
            ->setParameter('val', $value)
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }
    */
}
