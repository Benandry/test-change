<?php

namespace App\Repository;

use App\Entity\ListSinceId;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\OptimisticLockException;
use Doctrine\ORM\ORMException;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<ListSinceId>
 *
 * @method ListSinceId|null find($id, $lockMode = null, $lockVersion = null)
 * @method ListSinceId|null findOneBy(array $criteria, array $orderBy = null)
 * @method ListSinceId[]    findAll()
 * @method ListSinceId[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class ListSinceIdRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, ListSinceId::class);
    }

    /**
     * @throws ORMException
     * @throws OptimisticLockException
     */
    public function add(ListSinceId $entity, bool $flush = true): void
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
    public function remove(ListSinceId $entity, bool $flush = true): void
    {
        $this->_em->remove($entity);
        if ($flush) {
            $this->_em->flush();
        }
    }

    // /**
    //  * @return ListSinceId[] Returns an array of ListSinceId objects
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
    public function findOneBySomeField($value): ?ListSinceId
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
