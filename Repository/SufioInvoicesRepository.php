<?php

namespace App\Repository;

use App\Entity\SufioInvoices;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\OptimisticLockException;
use Doctrine\ORM\ORMException;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<SufioInvoices>
 *
 * @method SufioInvoices|null find($id, $lockMode = null, $lockVersion = null)
 * @method SufioInvoices|null findOneBy(array $criteria, array $orderBy = null)
 * @method SufioInvoices[]    findAll()
 * @method SufioInvoices[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class SufioInvoicesRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, SufioInvoices::class);
    }

    /**
     * @throws ORMException
     * @throws OptimisticLockException
     */
    public function add(SufioInvoices $entity, bool $flush = true): void
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
    public function remove(SufioInvoices $entity, bool $flush = true): void
    {
        $this->_em->remove($entity);
        if ($flush) {
            $this->_em->flush();
        }
    }

    // /**
    //  * @return SufioInvoices[] Returns an array of SufioInvoices objects
    //  */
    /*
    public function findByExampleField($value)
    {
        return $this->createQueryBuilder('s')
            ->andWhere('s.exampleField = :val')
            ->setParameter('val', $value)
            ->orderBy('s.id', 'ASC')
            ->setMaxResults(10)
            ->getQuery()
            ->getResult()
        ;
    }
    */

    /*
    public function findOneBySomeField($value): ?SufioInvoices
    {
        return $this->createQueryBuilder('s')
            ->andWhere('s.exampleField = :val')
            ->setParameter('val', $value)
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }
    */
}
