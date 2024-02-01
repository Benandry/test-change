<?php

namespace App\Repository;

use App\Entity\CaPays;
use App\Entity\Stores;
use App\Service\QueryService;
use DateTime;
use DateTimeZone;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\OptimisticLockException;
use Doctrine\ORM\ORMException;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<CaPays>
 *
 * @method CaPays|null find($id, $lockMode = null, $lockVersion = null)
 * @method CaPays|null findOneBy(array $criteria, array $orderBy = null)
 * @method CaPays[]    findAll()
 * @method CaPays[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class CaPaysRepository extends ServiceEntityRepository
{

    private QueryService $queryService;
    public function __construct(ManagerRegistry $registry, QueryService $queryService)
    {
        $this->queryService = $queryService;
        parent::__construct($registry, CaPays::class);
    }



    /**
     * @throws ORMException
     * @throws OptimisticLockException
     */
    public function add(CaPays $entity, bool $flush = true): void
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
    public function remove(CaPays $entity, bool $flush = true): void
    {
        $this->_em->remove($entity);
        if ($flush) {
            $this->_em->flush();
        }
    }


    private function queryBase()
    {
        $columns = [
            'c.id',
            'c.store',
            'c.sinceId',
            'c.numCommande',
            'c.numFacture',
            'c.emailClient',
            'c.subtotal',
            'c.shipping',
            'c.discount',
            'c.nomClient',
            'c.qty',
            'c.subtotalTax',
            'c.shippingTax',
            'c.tax',
            'c.grandTotal',
            'c.shippinInclTax',
            'c.method',
            'c.dateFacture',
            'c.countryId',
            'c.billingCompany',
            'c.shippingCompany',
            'c.shippingRegion',
            'c.billingRegion',
            'c.shippingPostcode',
            'c.shippingCity',
            'c.isVisible',
            'UPPER(s.name) store_name'
        ];
        return  $this->createQueryBuilder('c')
            ->select($columns)
            ->where("c.isVisible = 1")
            ->join(Stores::class, 's', 'WITH', 'c.store = s.id');
    }

    private function filterData($request, $storeId = null)
    {
        $columns = [
            'c.id',
            'c.store',
            'c.sinceId',
            'c.numCommande',
            'c.numFacture',
            'c.emailClient',
            'c.subtotal',
            'c.shipping',
            'c.discount',
            'c.nomClient',
            'c.qty',
            'c.subtotalTax',
            'c.shippingTax',
            'c.tax',
            'c.grandTotal',
            'c.shippinInclTax',
            'c.method',
            'c.dateFacture',
            'c.countryId',
            'c.billingCompany',
            'c.shippingCompany',
            'c.shippingRegion',
            'c.billingRegion',
            'c.shippingPostcode',
            'c.shippingCity',
            'c.isVisible',
            's.name'
        ];

        $qb = $this->queryBase();
        $qb =  $this->queryService->createFilterForm($qb, $columns, $storeId, $request['typeStore'], 'c');
        $qb->setParameter("word_to_search", "%" . $request['searchValue'] . "%");
        return  $this->queryService->paginationQuery($qb, $request);
    }

    /**
     * CA PAyS
     *
     * @param [type] $storeId
     * @return void
     */
    public function findCaPays($request)
    {
        if ($request['searchValue']) {
            return  $this->filterData($request);
        }
        $qb = $this->queryBase();
        $qb = $this->queryService->findAllByCountry($qb, "c.dateFacture", $request['typeStore'], "c");
        return  $this->queryService->paginationQuery($qb, $request);
    }




    /**
     * FInd All CA Par Pays all site EURO for yesterday
     *
     * @param [integer] $storeId
     */
    public function findAllCaPaysEuroForYesterday($storeId = null)
    {

        $yesterday = new \DateTime('yesterday', new \DateTimezone('UTC'));

        $clause = "";

        if ($storeId) {
            $clause = " AND  cp.store = $storeId ";
        } else {
            $clause = " AND  cp.store  IN (3, 4, 14, 15, 16, 20, 21, 22) ";
        }

        $rawSql = "SELECT
                    cp.store,
                    cp.sinceId,
                    cp.numCommande,
                    cp.numFacture,
                    cp.emailClient,
                    cp.subtotal,
                    cp.shipping,
                    cp.discount,
                    cp.nomClient,
                    cp.qty,
                    cp.subtotalTax,
                    cp.shippingTax,
                    cp.tax,
                    cp.grandTotal,
                    cp.shippinInclTax,
                    cp.method,
                    cp.dateFacture,
                    cp.countryId,
                    cp.billingCompany,
                    cp.shippingCompany,
                    cp.shippingRegion,
                    cp.billingRegion,
                    cp.shippingPostcode,
                    cp.shippingCity,
                    cp.isVisible
                    FROM App\Entity\CaPays cp
                    WHERE   cp.isVisible = 1   $clause
                    AND cp.dateFacture LIKE :yesterdayDate
                    ORDER BY cp.dateFacture DESC
                    ";

        $stmt = $this->getEntityManager()->createQuery($rawSql);

        $stmt->setParameter('yesterdayDate', $yesterday->format('Y-m-d') . '%');

        return $stmt->execute();
    }




    /**
     * Find Ca Pays Between date
     *
     * @param [type] $minDate
     * @param [type] $maxDate
     * @param [type] $except
     * @param [type] $asiaSite
     * @return void
     */
    public function findBetweenDate($request, $storeId = null)
    {
        if ($request['searchValue']) {
            return  $this->filterData($request, $storeId);
        }

        $minDate = date('Y-m-d', strtotime($request['minDate']));
        $maxDate = date('Y-m-d', strtotime($request['maxDate']));
        $qb = $this->queryBase()
            ->andWhere('SUBSTRING(c.dateFacture, 1, 10) BETWEEN :minDate AND :maxDate');

        $store = [3, 4, 14, 15, 16, 20, 21, 22]; // Valeurs de base pour $store
        if ($request['except']) {
            $store = [3, 4, 14, 15, 16, 20, 22];
        } elseif ($request['asiaSite']) {
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
        return  $this->queryService->paginationQuery($qb, $request);
    }



    public function findByStoreCaPays($storeId, $request)
    {
        if ($request['searchValue']) {
            return  $this->filterData($request, $storeId);
        }
        $qb = $this->queryBase();
        $qb = $this->queryService
            ->getDataFilterByStoreSelected($storeId, $qb, $request, 'c')
            ->setParameter('storeID', $storeId);
        return  $this->queryService->paginationQuery($qb, $request);
    }
}
