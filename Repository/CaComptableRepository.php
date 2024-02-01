<?php

namespace App\Repository;

use App\Entity\CaComptable;
use App\Entity\Stores;
use App\Service\QueryService;
use DateTime;
use DateTimeZone;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\OptimisticLockException;
use Doctrine\ORM\ORMException;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<CaComptable>
 *
 * @method CaComptable|null find($id, $lockMode = null, $lockVersion = null)
 * @method CaComptable|null findOneBy(array $criteria, array $orderBy = null)
 * @method CaComptable[]    findAll()
 * @method CaComptable[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class CaComptableRepository extends ServiceEntityRepository
{
    private QueryService  $queryService;
    public function __construct(ManagerRegistry $registry, QueryService  $queryService)
    {
        $this->queryService = $queryService;
        parent::__construct($registry, CaComptable::class);
    }

    /**
     * @throws ORMException
     * @throws OptimisticLockException
     */
    public function add(CaComptable $entity, bool $flush = true): void
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
    public function remove(CaComptable $entity, bool $flush = true): void
    {
        $this->_em->remove($entity);
        if ($flush) {
            $this->_em->flush();
        }
    }

    private function qbCaComptableBase()
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
            'c.billingVat',
            'c.customerVat',
            'c.compteComptable',
            'c.isVisible',
            'UPPER(s.name) store_name',
        ];
        return $this->createQueryBuilder('c')
            ->select($columns)->where("c.isVisible = 1")
            ->join(Stores::class, 's', 'WITH', 'c.store = s.id');
    }


    private function filterData($request, $storeId = null)
    {

        $word_to_search = "%" . $request['searchValue'] . "%";
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
            'c.billingVat',
            'c.customerVat',
            'c.compteComptable',
            'c.isVisible',
            "s.name"
        ];

        $qb = $this->qbCaComptableBase();
        $qb =  $this->queryService->createFilterForm($qb, $columns, $storeId, $request['typeStore'], 'c');
        $qb->setParameter("word_to_search", $word_to_search);
        return  $this->queryService->paginationQuery($qb, $request);
    }

    /**
     * Find all Ca comptabkle
     *
     * @param [type] $start
     * @param [type] $length
     * @param [type] $storeId
     * @return void
     */
    public function findCaComptable($request, $storeId = null)
    {
        if ($request['searchValue']) {
            return  $this->filterData($request, $storeId);
        }
        $qb = $this->qbCaComptableBase();
        $qb = $this->queryService->findAllByCountry($qb, "c.dateFacture", $request['typeStore'], "c");
        return  $this->queryService->paginationQuery($qb, $request);
    }

    /**
     * FInd All CA Comptable all site EUROPE for yesterday
     *
     * @param [integer] $storeId
     */
    public function findAllCaComptableEuroForYesterday($storeId = null)
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
                    ca.emailClient,
                    ca.subtotal,
                    ca.shipping,
                    ca.discount,
                    ca.nomClient,
                    ca.qty,
                    ca.subtotalTax,
                    ca.shippingTax,
                    ca.tax,
                    ca.grandTotal,
                    ca.shippinInclTax,
                    ca.countryId,
                    ca.method,
                    ca.dateFacture,
                    ca.billingVat,
                    ca.customerVat,
                    ca.isVisible,
                    ca.compteComptable
                    FROM App\Entity\CaComptable ca
                    WHERE ca.isVisible = 1  $clause  AND ca.dateFacture LIKE :yesterdayDate
                    ORDER BY ca.numFacture DESC
                    ";

        $stmt = $this->getEntityManager()->createQuery($rawSql);
        $stmt->setParameter('yesterdayDate', $yesterday->format('Y-m-d') . '%');
        return $stmt->execute();
    }

    public function findBetweenDate($request, $storeId = null)
    {
        if ($request['searchValue']) {
            return  $this->filterData($request, $storeId);
        }

        $minDate = date('Y-m-d', strtotime($request['minDate']));
        $maxDate = date('Y-m-d', strtotime($request['maxDate']));
        $qb = $this->qbCaComptableBase()
            ->where('SUBSTRING(c.dateFacture, 1, 10) BETWEEN :minDate AND :maxDate');
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

    public function findByStoreCaComptable($storeId, $request)
    {
        if ($request['searchValue']) {
            return  $this->filterData($request, $storeId);
        }
        $qb = $this->qbCaComptableBase();
        $qb = $this->queryService
            ->getDataFilterByStoreSelected($storeId, $qb, $request, 'c')
            ->setParameter('storeID', $storeId);
        return  $this->queryService->paginationQuery($qb, $request);
    }
}
