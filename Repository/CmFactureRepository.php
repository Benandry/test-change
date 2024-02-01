<?php

namespace App\Repository;

use App\Entity\CmFacture;
use App\Entity\Stores;
use App\Service\QueryService;
use DateTime;
use DateTimeZone;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\OptimisticLockException;
use Doctrine\ORM\ORMException;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<CmFacture>
 *
 * @method CmFacture|null find($id, $lockMode = null, $lockVersion = null)
 * @method CmFacture|null findOneBy(array $criteria, array $orderBy = null)
 * @method CmFacture[]    findAll()
 * @method CmFacture[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class CmFactureRepository extends ServiceEntityRepository
{
    private QueryService $queryService;
    public function __construct(ManagerRegistry $registry, QueryService $queryService)
    {
        parent::__construct($registry, CmFacture::class);
        $this->queryService = $queryService;
    }

    /**
     * @throws ORMException
     * @throws OptimisticLockException
     */
    public function add(CmFacture $entity, bool $flush = true): void
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
    public function remove(CmFacture $entity, bool $flush = true): void
    {
        $this->_em->remove($entity);
        if ($flush) {
            $this->_em->flush();
        }
    }

    private function getSelectColumn()
    {
        $columns = [
            'cm.id',
            'cm.store',
            'cm.sinceId',
            'cm.numCreditmemo',
            'cm.nomClient',
            'cm.billingClient',
            'cm.dateCreditmemo',
            'cm.emailClient',
            'cm.subtotal',
            'cm.shipping',
            'UPPER(s.name) store_name'
        ];

        $qb =  $this->createQueryBuilder('cm')->select($columns);
        $orX = $qb->expr()->orX();
        $orX->add($qb->expr()->like("cm.numCreditmemo", ":vnm_euro"));
        $qb->andWhere($orX)->setParameter('vnm_euro', '%VNM-AV%');
        return $qb->andWhere($orX)
            ->join(Stores::class, 's', 'WITH', 'cm.store = s.id');
    }

    private function filterData($request, $store = null)
    {
        $word_to_search = "%" . $request['searchValue'] . "%";
        $columns = [
            'cm.id',
            'cm.store',
            'cm.sinceId',
            'cm.numCreditmemo',
            'cm.nomClient',
            'cm.billingClient',
            'cm.dateCreditmemo',
            'cm.emailClient',
            'cm.subtotal',
            'cm.shipping',
            's.name'
        ];
        $qb = $this->getSelectColumn();
        $qb =  $this->queryService->createFilterForm($qb, $columns, $store, $request['typeStore'], 'cm');
        $qb->setParameter("word_to_search", $word_to_search);
        return  $this->queryService->paginationQuery($qb, $request);
    }



    public function findByCmFacture($request)
    {
        if ($request['searchValue']) {
            return  $this->filterData($request);
        }

        $qb = $this->getSelectColumn();
        $qb = $this->queryService->findAllByCountry($qb, "cm.dateCreditmemo", $request['typeStore'], "cm");
        return  $this->queryService->paginationQuery($qb, $request);
    }


    public function createQueryCmFacture($storeId)
    {
        $datetime = new DateTime('now', new DateTimeZone('Europe/Paris'));
        $monthYearCurrent = $datetime->format('Y') . '-' . $datetime->format('m') . '%';


        $clause = "";

        if ($storeId) {
            $clause = " cm.store = $storeId AND ";
        } else {
            $clause = "cm.store  IN (3, 4, 14, 15, 16, 20, 22) AND";
        }
        $rawSql = "SELECT 
                    DISTINCT  cm.id,
                    cm.store,
                    cm.sinceId,
                    cm.numCreditmemo,
                    cm.nomClient,
                    cm.billingClient,
                    cm.dateCreditmemo,
                    cm.emailClient,
                    cm.subtotal,
                    cm.shipping
                    FROM App\Entity\CmFacture cm
                    WHERE   $clause 
                    cm.dateCreditmemo LIKE :monthYearCurrent 
                    AND  cm.numCreditmemo LIKE :vnm_euro
                    ORDER BY cm.dateCreditmemo DESC
                    ";
        $stmt = $this->getEntityManager()->createQuery($rawSql);
        $stmt->setParameter('monthYearCurrent', $monthYearCurrent);
        $stmt->setParameter('vnm_euro', '%VNM-AV%');
        return $stmt;
    }

    /**
     * FInd All CM Par Facture all site EUROPE for yesterday
     *
     * @param [integer] $storeId
     */
    public function findAllCmFactureEuroForYesterday($storeId = null)
    {

        $yesterday = new \DateTime('yesterday', new \DateTimezone('UTC'));

        $clause = "";

        if ($storeId) {
            $clause = " cm.store = $storeId AND ";
        } else {
            $clause = "cm.store  IN (3, 4, 14, 15, 16, 20, 22) AND";
        }
        $rawSql = "SELECT
                    DISTINCT  cm.id,
                    cm.store,
                    cm.sinceId,
                    cm.numCreditmemo,
                    cm.nomClient,
                    cm.billingClient,
                    cm.dateCreditmemo,
                    cm.emailClient,
                    cm.subtotal,
                    cm.shipping
                    FROM App\Entity\CmFacture cm
                    WHERE   $clause cm.dateCreditmemo LIKE :yesterdayDate
                    ORDER BY cm.dateCreditmemo DESC
                    ";

        $stmt = $this->getEntityManager()->createQuery($rawSql);
        $stmt->setParameter('yesterdayDate', $yesterday->format('Y-m-d') . '%');
        return $stmt->execute();
    }



    public function findByCmFactureExceptUk($request)
    {
        dd($request);
        $qb = $this->getSelectColumn();
        $qb = $this->queryService->findAllByCountry($qb, "cm.dateCreditmemo", 2, "cm");
        return  $this->queryService->paginationQuery($qb, $request);
    }


    public function findBetweenDate($request, $storeId = null)
    {
        if ($request['searchValue']) {
            return  $storeId ? $this->filterData($request, $storeId) : $this->filterData($request);
        }
        $qb = $this->getSelectColumn()
            ->where('SUBSTRING(cm.dateCreditmemo, 1, 10) BETWEEN :minDate AND :maxDate');

        if ($request['except']) {
            $store = [3, 4, 14, 15, 16, 20, 22];
            $qb->andWhere('cm.numCreditmemo LIKE :vnm_euro')
                ->setParameter('vnm_euro', '%VNM-AV%');
        } elseif ($request['asiaSite']) {
            $store = [23, 24, 25];
        } else {
            $store = [3, 4, 14, 15, 16, 20, 21, 22];
            $qb->andWhere('cm.numCreditmemo LIKE :vnm_euro')
                ->setParameter('vnm_euro', '%VNM-AV%');
        }

        if ($storeId) {
            $qb->andWhere('cm.store = :storeId');
        } else {
            $qb->andWhere($qb->expr()->in('cm.store', $store));
        }
        $minDate = date('Y-m-d', strtotime($request['minDate']));
        $maxDate = date('Y-m-d', strtotime($request['maxDate']));
        $qb->orderBy("cm.dateCreditmemo", "DESC")
            ->setParameter('minDate', $minDate)
            ->setParameter('maxDate', $maxDate);
        if ($storeId) {
            $qb->setParameter('storeId', $storeId);     # code...
        }
        return  $this->queryService->paginationQuery($qb, $request);
    }


    /**
     * Filter by soter
     *
     * @param [integer] $storeId
     */
    private function queryBuilderCmFacture($storeIds, $request)
    {
        $qb = $this->getSelectColumn();
        $qb = $this->queryService->getDataFilterByStoreSelected($storeIds, $qb, $request, 'cm');

        if (!is_array($storeIds)) {
            $store = [23, 24, 25];
            if (!in_array($storeIds, $store)) {
                $qb->andWhere('cm.numCreditmemo LIKE :vnm_euro')
                    ->setParameter('vnm_euro', '%VNM-AV%');
            }
        }
        $qb->setParameter('storeID', $storeIds);
        return  $qb;
    }
    public function findByStoreCmFacture($storeId, $request)
    {
        if ($request['searchValue']) {
            return  $this->filterData($request, $storeId);
        }
        $qb = $this->queryBuilderCmFacture($storeId, $request);
        return  $this->queryService->paginationQuery($qb, $request);
    }

    public function getYesterdayTotalSubtotalCreditMemos($stores)
    {

        $yesterday = new \DateTime('yesterday', new \DateTimezone('UTC'));

        $queryBuilder = $this->createQueryBuilder('cmf');

        $queryBuilder
            ->select('SUM(cmf.subtotal) AS totalSubtotalCmFacture')
            ->where('cmf.dateCreditmemo LIKE :yesterday');

        if (is_array($stores)) {
            $storesString = implode(',', $stores);
            $queryBuilder->andWhere("cmf.store IN ({$storesString})");
        } else {
            $queryBuilder->andWhere('cmf.store = :store')
                ->setParameter('store', $stores);
        }

        $queryBuilder->setParameter('yesterday', $yesterday->format('Y-m-d') . '%');

        return $queryBuilder->getQuery()->getSingleScalarResult();
    }
}
