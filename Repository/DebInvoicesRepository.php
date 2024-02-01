<?php

namespace App\Repository;

use App\Entity\DebInvoices;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\OptimisticLockException;
use Doctrine\ORM\ORMException;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<DebInvoices>
 *
 * @method DebInvoices|null find($id, $lockMode = null, $lockVersion = null)
 * @method DebInvoices|null findOneBy(array $criteria, array $orderBy = null)
 * @method DebInvoices[]    findAll()
 * @method DebInvoices[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class DebInvoicesRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, DebInvoices::class);
    }

    /**
     * @throws ORMException
     * @throws OptimisticLockException
     */
    public function add(DebInvoices $entity, bool $flush = true): void
    {
        $this->_em->persist($entity);
        if ($flush) {
            $this->_em->flush();
        }
    }

    /**
     * @throws ORMException
     * @throws OptimisticLockException
     */
    public function remove(DebInvoices $entity, bool $flush = true): void
    {
        $this->_em->remove($entity);
        if ($flush) {
            $this->_em->flush();
        }
    }

    // /**
    //  * @return DebInvoices[] Returns an array of Deb objects
    //  */
    /*
    public function findByExampleField($value)
    {
        return $this->createQueryBuilder('d')
            ->andWhere('d.exampleField = :val')
            ->setParameter('val', $value)
            ->orderBy('d.id', 'ASC')
            ->setMaxResults(10)
            ->getQuery()
            ->getResult()
        ;
    }
    */

    /*
    public function findOneBySomeField($value): ?Deb
    {
        return $this->createQueryBuilder('d')
            ->andWhere('d.exampleField = :val')
            ->setParameter('val', $value)
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }
    */
}
