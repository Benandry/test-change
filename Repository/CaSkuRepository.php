<?php

namespace App\Repository;

use App\Entity\CaSku;
use App\Service\QueryService;
use DateTime;
use DateTimeZone;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\OptimisticLockException;
use Doctrine\ORM\ORMException;
use Doctrine\ORM\Query\ResultSetMapping;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<CaSku>
 *
 * @method CaSku|null find($id, $lockMode = null, $lockVersion = null)
 * @method CaSku|null findOneBy(array $criteria, array $orderBy = null)
 * @method CaSku[]    findAll()
 * @method CaSku[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class CaSkuRepository extends ServiceEntityRepository
{
    private QueryService $queryService;
    public function __construct(ManagerRegistry $registry, QueryService $queryService)
    {
        parent::__construct($registry, CaSku::class);
        $this->queryService = $queryService;
    }

    /**
     * @throws ORMException
     * @throws OptimisticLockException
     */
    public function add(CaSku $entity, bool $flush = true): void
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
    public function remove(CaSku $entity, bool $flush = true): void
    {
        $this->_em->remove($entity);
        if ($flush) {
            $this->_em->flush();
        }
    }
    /**
     * Search Ca SKU
     *
     * @param [type] $value
     * @param [type] $storeId
     * @return void
     */
    public function findByWordField($value, $start, $length, $typeStore, $storeId = null)
    {
        $stmt = $this->createQueryWordFieldSearch($value, $typeStore, $storeId)
            ->setFirstResult($start)
            ->setMaxResults($length);
        $result = $stmt->getQuery()->getResult();
        return [
            $result,
            $this->createQueryWordFieldSearch($value, $typeStore, $storeId)->getQuery()->getResult()
        ];
    }

    public function createQueryWordFieldSearch($value, $typeStore, $storeId)
    {
        $word_to_search = "%" . $value . "%";

        $columns = [
            'c.id',
            'c.store',
            'c.sinceId',
            'c.numCommande',
            'c.numFacture',
            'c.subtotal',
            'c.discount',
            'c.nomClient',
            'c.qty',
            'c.subtotalTax',
            'c.tax',
            'c.dateFacture',
            'c.sku',
            'c.productId',
            'c.name',
            'c.shippingCompany',
            'c.billingCompany',
            'c.isVisible'
        ];

        $qb = $this->createQueryBuilder('c')->select($columns)->where('c.isVisible = 1');
        $qb =  $this->queryService->search($qb, $columns, $storeId, $typeStore, 'c.dateFacture');
        $qb->setParameter("word_to_search", $word_to_search);
        return $qb;
    }


    /**
     * Find all casku 
     *
     * @param [type] $storeId
     * @return void
     */
    public function findStoreVisibleById($start, $length, $storeId = null)
    {
        $stmt = $this->createQueryBuilderCaSku($storeId)
            ->setFirstResult($start) // Le premier résultat à retourner (dépend de $start)
            ->setMaxResults($length);
        return [
            $stmt->execute(),
            $this->createQueryBuilderCaSku($storeId)->execute()
        ];
    }
    public function createQueryBuilderCaSku($storeId)
    {
        $datetime = new DateTime('now', new DateTimeZone('Europe/Paris'));

        $monthYearCurrent = $datetime->format('Y') . '-' . $datetime->format('m') . '%';

        $clause = "";

        if ($storeId) {
            $clause = " AND  ca.store = $storeId ";
        } else {
            $clause = " AND  ca.store  IN (3, 4, 14, 15, 16, 20, 22) ";
        }

        $rawSql = "SELECT 
                    ca.id,
                    ca.store,
                    ca.sinceId,
                    ca.numCommande,
                    ca.numFacture,
                    ca.subtotal,
                    ca.discount,
                    ca.nomClient,
                    ca.qty,
                    ca.subtotalTax,
                    ca.tax,
                    ca.dateFacture,
                    ca.sku,
                    ca.productId,
                    ca.name,
                    ca.shippingCompany,
                    ca.billingCompany,
                    ca.isVisible
                    FROM App\Entity\CaSku ca
                    WHERE  ca.isVisible = 1   $clause  
                    AND ca.dateFacture LIKE :monthYearCurrent
                    ORDER BY ca.dateFacture DESC
                    ";

        $stmt = $this->getEntityManager()->createQuery($rawSql);
        $stmt->setParameter('monthYearCurrent', $monthYearCurrent);
        return $stmt;
    }


    /**
     * FInd All CA Par SKU all site EUROPE for yesterday
     *
     * @param [integer] $storeId
     */
    public function findAllCaSkuEuroForYesterday($storeId = null)
    {

        $yesterday = new \DateTime('yesterday', new \DateTimezone('UTC'));

        $clause = "";

        if ($storeId) {
            $clause = " AND  ca.store = $storeId ";
        } else {
            $clause = " AND  ca.store  IN (3, 4, 14, 15, 16, 20, 21, 22) ";
        }

        $rawSql = "SELECT 
                    ca.id,
                    ca.store,
                    ca.sinceId,
                    ca.numCommande,
                    ca.numFacture,
                    ca.subtotal,
                    ca.discount,
                    ca.nomClient,
                    ca.qty,
                    ca.subtotalTax,
                    ca.tax,
                    ca.dateFacture,
                    ca.sku,
                    ca.productId,
                    ca.name,
                    ca.shippingCompany,
                    ca.billingCompany,
                    ca.isVisible
                    FROM App\Entity\CaSku ca
                    WHERE  ca.isVisible = 1   $clause  
                    AND ca.dateFacture LIKE :yesterdayDate
                    ORDER BY ca.dateFacture DESC
                    ";

        $stmt = $this->getEntityManager()->createQuery($rawSql);
        // $stmt->setParameter('yesterdayDate', '2023-09-13%');
        $stmt->setParameter('yesterdayDate', $yesterday->format('Y-m-d') . '%');
        return $stmt->execute();
    }


    /**
     * Find CASKU EXCEPT UK
     *
     * @return void
     */
    public function findCaSkuExceptUk($start, $length)
    {
        $stmt = $this->createQueryBuilderCaSkuExceptUk()
            ->setFirstResult($start) // Le premier résultat à retourner (dépend de $start)
            ->setMaxResults($length);
        return [$stmt->execute(), $this->createQueryBuilderCaSkuExceptUk()->execute()];
    }
    public function createQueryBuilderCaSkuExceptUk()
    {
        $datetime = new DateTime('now', new DateTimeZone('Europe/Paris'));

        $monthYearCurrent = $datetime->format('Y') . '-' . $datetime->format('m') . '%';
        $rawSql = "SELECT 
                    ca.id,
                    ca.store,
                    ca.sinceId,
                    ca.numCommande,
                    ca.numFacture,
                    ca.subtotal,
                    ca.discount,
                    ca.nomClient,
                    ca.qty,
                    ca.subtotalTax,
                    ca.tax,
                    ca.dateFacture,
                    ca.sku,
                    ca.productId,
                    ca.name,
                    ca.shippingCompany,
                    ca.billingCompany,
                    ca.isVisible
                    FROM App\Entity\CaSku ca
                    WHERE  ca.isVisible = 1   
                    AND ca.store IN (3, 4, 14, 15, 16, 20, 22) 
                    AND ca.dateFacture LIKE :monthYearCurrent
                    ORDER BY ca.dateFacture DESC
                    ";

        $stmt = $this->getEntityManager()->createQuery($rawSql);
        $stmt->setParameter('monthYearCurrent', $monthYearCurrent);
        return $stmt;
    }


    /**
     * Find betwenn two date
     *
     * @param [type] $minDate
     * @param [type] $maxDate
     * @param [type] $except
     * @param [type] $asiaSite
     * @return void
     */
    public function findAllCaParSKUBetweenTwoDates($minDate, $maxDate, $except, $asiaSite, $start, $length, $storeId = null)
    {
        $qb =  $this->createQueryCaParSKUBetweenTwoDates($minDate, $maxDate, $except, $asiaSite, $storeId)
            ->setFirstResult($start) // Le premier résultat à retourner (dépend de $start)
            ->setMaxResults($length);

        $query = $qb->getQuery();

        return [
            $query->getResult(),
            $this->createQueryCaParSKUBetweenTwoDates($minDate, $maxDate, $except, $asiaSite, $storeId)->getQuery()->getResult()
        ];
    }

    public function createQueryCaParSKUBetweenTwoDates($minDate, $maxDate, $except, $asiaSite, $storeId)
    {
        $minDate = date('Y-m-d', strtotime($minDate));
        $maxDate = date('Y-m-d', strtotime($maxDate));

        $columns = [
            'c.id',
            'c.store',
            'c.sinceId',
            'c.numCommande',
            'c.numFacture',
            'c.subtotal',
            'c.discount',
            'c.nomClient',
            'c.qty',
            'c.subtotalTax',
            'c.tax',
            'c.dateFacture',
            'c.sku',
            'c.productId',
            'c.name',
            'c.shippingCompany',
            'c.billingCompany',
            'c.isVisible'
        ];

        $qb = $this->createQueryBuilder('c');

        $qb->select($columns)
            ->where('c.isVisible = 1')
            ->andWhere('SUBSTRING(c.dateFacture, 1, 10) BETWEEN :minDate AND :maxDate');
        $store = [3, 4, 14, 15, 16, 20, 21, 22]; // Valeurs de base pour $store
        if ($except) {
            $store = [3, 4, 14, 15, 16, 20, 22];
        } elseif ($asiaSite) {
            $store = [23, 24, 25];
        }
        if ($storeId) {
            $qb->andWhere('c.store = :storeId');
        } else {
            $qb->andWhere($qb->expr()->in('c.store', $store));
        }
        $qb->orderBy("c.dateFacture", "DESC")
            ->setParameter('minDate', $minDate)
            ->setParameter('maxDate', $maxDate);
        if ($storeId) {
            $qb->setParameter('storeId', $storeId);     # code...
        }


        return $qb;
    }



    /**
     * find all CA SKU ASIA
     */
    public function findCaSkuAsia($start, $length)
    {
        $stmt = $this->createQueryBuilderCaSkuAsia()
            ->setFirstResult($start) // Le premier résultat à retourner (dépend de $start)
            ->setMaxResults($length);
        return [
            $stmt->execute(),
            $this->createQueryBuilderCaSkuAsia()->execute()
        ];
    }
    public function createQueryBuilderCaSkuAsia()
    {
        $datetime = new DateTime('now', new DateTimeZone('Europe/Paris'));

        $monthYearCurrent = $datetime->format('Y') . '-' . $datetime->format('m') . '%';
        $rawSql = "SELECT 
                    ca.id,
                    ca.store,
                    ca.sinceId,
                    ca.numCommande,
                    ca.numFacture,
                    ca.subtotal,
                    ca.discount,
                    ca.nomClient,
                    ca.qty,
                    ca.subtotalTax,
                    ca.tax,
                    ca.dateFacture,
                    ca.sku,
                    ca.productId,
                    ca.name,
                    ca.shippingCompany,
                    ca.billingCompany,
                    ca.isVisible
                    FROM App\Entity\CaSku ca
                    WHERE  ca.isVisible = 1   
                    AND ca.store IN (23,24,25)  -- ROW,RU,JPca.store IN (3, 4, 14, 15, 16, 20, 22) 
                    AND ca.dateFacture LIKE :monthYearCurrent
                    ORDER BY ca.dateFacture DESC
                    ";

        $stmt = $this->getEntityManager()->createQuery($rawSql);
        $stmt->setParameter('monthYearCurrent', $monthYearCurrent);
        return $stmt;
    }

    /**
     * Filter by soter
     *
     * @param [integer] $storeId
     * 
     */
    public function queryBuilderCaSku($storeIds, $minDate, $maxDate)
    {

        $columns = [
            'c.id',
            'c.store',
            'c.sinceId',
            'c.numCommande',
            'c.numFacture',
            'c.subtotal',
            'c.discount',
            'c.nomClient',
            'c.qty',
            'c.subtotalTax',
            'c.tax',
            'c.dateFacture',
            'c.sku',
            'c.productId',
            'c.name',
            'c.shippingCompany',
            'c.billingCompany',
            'c.isVisible'
        ];
        $qb = $this->createQueryBuilder('c')->select($columns)->where("c.isVisible = 1");
        $qb = $this->queryService->getDataFilterByStoreSelected($storeIds, $qb, $minDate, $maxDate, 'c');
        $qb->setParameter('storeID', $storeIds);
        return $qb;
    }
    public function findByStoreCaSku($storeId, $start, $length, $minDate = null, $maxDate = null)
    {
        $stmt = $this->queryBuilderCaSku($storeId, $minDate, $maxDate)
            ->setFirstResult($start) // Le premier résultat à retourner (dépend de $start)
            ->setMaxResults($length)
            ->getQuery();
        return [$stmt->getResult(), $this->queryBuilderCaSku($storeId, $minDate, $maxDate)->getQuery()->getResult()];
    }


    public function findAllCaSkusInTheLast30DaysByStore($storeId, $limit)
    {

        if ($storeId === 26) {
            $sql = "SELECT
                cs.store,
                CASE
                    WHEN SUBSTRING_INDEX(LOWER(cs.sku), '-', -1) = LOWER(REPLACE(s.name, ' ', '')) 
                    THEN REPLACE(cs.sku, CONCAT('-', SUBSTRING_INDEX(cs.sku, '-', -1)), '')
                    ELSE cs.sku
                END AS skuWithoutSize,
                SUM(
                    CASE
                        WHEN SUBSTRING_INDEX(LOWER(cs.sku), '-', -1) = LOWER(REPLACE(s.name, ' ', '')) 
                        THEN cs.qty / 2
                        ELSE cs.qty
                    END
                ) AS totalQuantity
                FROM ca_sku cs
                LEFT JOIN size s ON SUBSTRING_INDEX(LOWER(cs.sku), '-', -1) = LOWER(s.name)
                WHERE cs.store = :storeId
                AND cs.date_facture >= :cutoffDate
                GROUP BY skuWithoutSize
                ORDER BY cs.store, totalQuantity DESC
                LIMIT $limit
                OFFSET 1
            ";
        } else {
            $sql = "SELECT
                cs.store,
                CASE
                    WHEN SUBSTRING_INDEX(LOWER(cs.sku), '-', -1) = LOWER(REPLACE(s.name, ' ', '')) 
                    THEN REPLACE(cs.sku, CONCAT('-', SUBSTRING_INDEX(cs.sku, '-', -1)), '')
                    ELSE cs.sku
                END AS skuWithoutSize,
                SUM(
                    CASE
                        WHEN SUBSTRING_INDEX(LOWER(cs.sku), '-', -1) = LOWER(REPLACE(s.name, ' ', '')) 
                        THEN cs.qty / 2
                        ELSE cs.qty
                    END
                ) AS totalQuantity
                FROM ca_sku cs
                LEFT JOIN size s ON SUBSTRING_INDEX(LOWER(cs.sku), '-', -1) = LOWER(REPLACE(s.name, ' ', ''))
                WHERE cs.store = :storeId
                AND cs.date_facture >= :cutoffDate
                GROUP BY skuWithoutSize
                ORDER BY cs.store, totalQuantity DESC
                LIMIT $limit
            ";
        }


        $rsm = new ResultSetMapping();

        $rsm->addScalarResult('store', 'store', 'integer');
        $rsm->addScalarResult('skuWithoutSize', 'skuWithoutSize');
        $rsm->addScalarResult('totalQuantity', 'totalQuantity', 'integer');

        $query = $this->getEntityManager()->createNativeQuery($sql, $rsm);

        $query->setParameter('storeId', $storeId);

        $query->setParameter('cutoffDate', new \DateTime('-30 days'));

        return $query->getResult();
    }
}
