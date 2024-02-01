<?php

namespace App\Repository;

use App\Entity\CaFacture;
use App\Entity\Stores;
use App\Service\QueryService;
use DateTime;
use DateTimeZone;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\OptimisticLockException;
use Doctrine\ORM\ORMException;
use Doctrine\ORM\Query;
use Doctrine\ORM\Query\ResultSetMapping;
use Doctrine\ORM\QueryBuilder;
use Doctrine\Persistence\ManagerRegistry;


/**
 * @extends ServiceEntityRepository<CaFacture>
 *
 * @method CaFacture|null find($id, $lockMode = null, $lockVersion = null)
 * @method CaFacture|null findOneBy(array $criteria, array $orderBy = null)
 * @method CaFacture[]    findAll()
 * @method CaFacture[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class CaFactureRepository extends ServiceEntityRepository
{

    private queryService $queryService;
    public function __construct(ManagerRegistry $registry, QueryService $queryService)
    {
        $this->queryService = $queryService;
        parent::__construct($registry, CaFacture::class);
    }

    /**
     * @throws ORMException
     * @throws OptimisticLockException
     */
    public function add(CaFacture $entity, bool $flush = true): void
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
    public function remove(CaFacture $entity, bool $flush = true): void
    {
        $this->_em->remove($entity);
        if ($flush) {
            $this->_em->flush();
        }
    }

    private function  getSelectColumn(): QueryBuilder
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
            'c.customerBalance',
            'c.giftCards',
            'c.rewardCurrency',
            'c.method',
            'c.dateFacture',
            'c.isVisible',
            'UPPER(s.name) store_name'
        ];
        return  $this->createQueryBuilder('c')
            ->select($columns)
            ->where("c.isVisible = 1")
            ->join(Stores::class, 's', 'WITH', 'c.store = s.id');
    }

    public function filterData($request, $storeId = null)
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
            'c.customerBalance',
            'c.giftCards',
            'c.rewardCurrency',
            'c.method',
            'c.dateFacture',
            'c.isVisible',
            's.name'
        ];
        $qb = $this->getSelectColumn();
        $qb =  $this->queryService->createFilterForm($qb, $columns, $storeId, $request['typeStore'], 'c');
        $qb->setParameter("word_to_search", "%" . $request['searchValue'] . "%");
        return  $this->queryService->paginationQuery($qb, $request);
    }

    /**
     * FInd All CA facture all site EURO
     *
     * @param [integer] $storeId
     */
    public function findAllCaFactureEuro($storeId = null)
    {
        $datetime = new DateTime('now', new DateTimeZone('Europe/Paris'));
        $monthYearCurrent = $datetime->format('Y') . '-' . $datetime->format('m') . '%';
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
                    ca.customerBalance,
                    ca.giftCards,
                    ca.rewardCurrency,
                    ca.method,
                    ca.dateFacture,
                    ca.isVisible
                    FROM App\Entity\CaFacture ca
                    WHERE  ca.isVisible = 1 $clause  AND ca.dateFacture LIKE :monthYearCurrent
                    ORDER BY ca.dateFacture DESC
                    ";

        $stmt = $this->getEntityManager()->createQuery($rawSql);
        $stmt->setParameter('monthYearCurrent', $monthYearCurrent);
        // $stmt->setParameter('monthYearCurrent', '2023-09%'); // Juste pour test
        return $stmt->execute();
    }

    /**
     * FInd All CA facture all site EURO for Yesterday
     *
     * @param [integer] $storeId
     */
    public function findAllCaFactureEuroForYesterday($storeId = null)
    {
        //All invoices 24H
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
                    ca.customerBalance,
                    ca.giftCards,
                    ca.rewardCurrency,
                    ca.method,
                    ca.dateFacture,
                    ca.isVisible
                    FROM App\Entity\CaFacture ca
                    WHERE  ca.isVisible = 1 $clause  AND ca.dateFacture LIKE :yesterdayDate
                    ORDER BY ca.dateFacture DESC
                    ";

        $stmt = $this->getEntityManager()->createQuery($rawSql);
        $stmt->setParameter('yesterdayDate', $yesterday->format('Y-m-d') . '%');
        return $stmt->execute();
    }


    public function findCaFacture($request)
    {
        if ($request['searchValue']) {
            return  $this->filterData($request);
        }
        $qb = $this->getSelectColumn();
        $qb = $this->queryService->findAllByCountry($qb, "c.dateFacture", $request['typeStore'], 'c');
        return  $this->queryService->paginationQuery($qb, $request);
    }

    public function findAllCaFactureBetweenTwoDates($request, $storeId = null)
    {
        if ($request['searchValue']) {
            return $storeId ? $this->filterData($request, $storeId) : $this->filterData($request);
        }
        $smt = $this->getSelectColumn();
        $smt = $this->queryService->getBetweenDate($smt, $storeId, $request, 'c.dateFacture');
        return  $this->queryService->paginationQuery($smt, $request);
    }

    public function findCaFactureByStore($storeId, $request)
    {
        if ($request['searchValue']) {
            return  $this->filterData($request);
        }

        $qb = $this->getSelectColumn();
        $qb = $this->queryService->getStore($storeId, $qb, $request, 'c.dateFacture');
        $qb->setParameter('storeID', $storeId);
        return  $this->queryService->paginationQuery($qb, $request);
    }

    public function getYesterdayTotalSubtotalCaFacture($storeIds)
    {

        $yesterday = new \DateTime('yesterday', new \DateTimezone('UTC'));

        $sql = "SELECT
                    SUM(totalSubtotalCaFacture) AS grandTotalSubtotalCaFacture
                FROM
                (
                    SELECT
                        s.name,
                        SUM(cf.subtotal) AS totalSubtotalCaFacture
                    FROM
                        ca_facture cf
                    INNER JOIN
                        stores s ON cf.store = s.id
                    WHERE
                        cf.date_facture LIKE :yesterdayDate
                        AND cf.store IN ($storeIds)
                    GROUP BY
                        s.name
                ) AS subquery;
        ";

        $rsm = new ResultSetMapping();

        $rsm->addScalarResult('grandTotalSubtotalCaFacture', 'grandTotalSubtotalCaFacture');

        $query = $this->getEntityManager()->createNativeQuery($sql, $rsm);

        $query->setParameter('yesterdayDate', $yesterday->format('Y-m-d') . '%');

        return $query->getResult();
    }
}
