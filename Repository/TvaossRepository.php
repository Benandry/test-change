<?php

namespace App\Repository;

use App\Entity\Tvaoss;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\OptimisticLockException;
use Doctrine\ORM\ORMException;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Tvaoss>
 *
 * @method Tvaoss|null find($id, $lockMode = null, $lockVersion = null)
 * @method Tvaoss|null findOneBy(array $criteria, array $orderBy = null)
 * @method Tvaoss[]    findAll()
 * @method Tvaoss[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class TvaossRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Tvaoss::class);
    }

    /**
     * @throws ORMException
     * @throws OptimisticLockException
     */
    public function add(Tvaoss $entity, bool $flush = true): void
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
    public function remove(Tvaoss $entity, bool $flush = true): void
    {
        $this->_em->remove($entity);
        if ($flush) {
            $this->_em->flush();
        }
    }

    // /**
    //  * @return Tvaoss[] Returns an array of Tvaoss objects
    //  */
    /*
    public function findByExampleField($value)
    {
        return $this->createQueryBuilder('t')
            ->andWhere('t.exampleField = :val')
            ->setParameter('val', $value)
            ->orderBy('t.id', 'ASC')
            ->setMaxResults(10)
            ->getQuery()
            ->getResult()
        ;
    }
    */

    /*
    public function findOneBySomeField($value): ?Tvaoss
    {
        return $this->createQueryBuilder('t')
            ->andWhere('t.exampleField = :val')
            ->setParameter('val', $value)
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }
    */

    public function findByLastTvaoss($date, $storeId=null) {
        $query = $this->createQueryBuilder('l')
            ->andWhere('l.date_facture > :date_facture')
            ->setParameter('date_facture', $date);

        if($storeId) {
            $query->andWhere('l.store = :store')
            ->setParameter('store', $storeId);
        }
        return $query->getQuery()->getResult();
    }

    public function findByTvaoss($date, $storeId=null) {
        $fields = array('l.date_facture','l.num_facture','l.type_operation','l.type_bien','l.type_service','l.qty','l.prix_unitaire','l.montant_total_ht','l.taux_tva','l.montant_tva','l.montant_ttc','l.devise','l.date_livraison','l.pays_depart','l.pays_arrivee','l.client_addresse','l.nom_client','l.date_paiement','l.montant_paiement','l.accompte','l.lien_facture');
        $query = $this->createQueryBuilder('l')
            ->select($fields)
            ->andWhere('l.date_facture > :date_facture')
            ->setParameter('date_facture', $date);
        if($storeId) {
            $query->andWhere('l.store = :store')
            ->setParameter('store', $storeId);
        }
        
        return $query->getQuery()->getArrayResult();
    }
}
