<?php

namespace App\Repository;

use App\Entity\CmSku;
use App\Entity\Stores;
use App\Service\QueryService;
use DateTime;
use DateTimeZone;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\OptimisticLockException;
use Doctrine\ORM\ORMException;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<CmSku>
 *
 * @method CmSku|null find($id, $lockMode = null, $lockVersion = null)
 * @method CmSku|null findOneBy(array $criteria, array $orderBy = null)
 * @method CmSku[]    findAll()
 * @method CmSku[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class CmSkuRepository extends ServiceEntityRepository
{
    private QueryService $queryService;
    public function __construct(ManagerRegistry $registry, QueryService $queryService)
    {
        $this->queryService = $queryService;
        parent::__construct($registry, CmSku::class);
    }

    /**
     * @throws ORMException
     * @throws OptimisticLockException
     */
    public function add(CmSku $entity, bool $flush = true): void
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
    public function remove(CmSku $entity, bool $flush = true): void
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
            'cm.numCommande',
            'cm.numCreditmemo',
            'cm.nomClient',
            'cm.qty',
            'cm.rowTotalTax',
            'cm.tax',
            'cm.dateCreditmemo',
            'cm.sku',
            'cm.productId',
            'cm.name',
            'UPPER(s.name) store_name'
        ];
        return  $this->createQueryBuilder('cm')
            ->select($columns)
            ->join(Stores::class, 's', 'WITH', 'cm.store = s.id');
    }
    private function filterData($request, $storeId = null)
    {
        $word_to_search = "%" . $request['searchValue'] . "%";
        $columns = [
            'cm.id',
            'cm.store',
            'cm.sinceId',
            'cm.numCommande',
            'cm.numCreditmemo',
            'cm.nomClient',
            'cm.qty',
            'cm.rowTotalTax',
            'cm.tax',
            'cm.dateCreditmemo',
            'cm.sku',
            'cm.productId',
            'cm.name',
            's.name'
        ];

        $qb = $this->getSelectColumn();
        $qb =  $this->queryService->createFilterForm($qb, $columns, $storeId, $request['typeStore'], 'cm');
        $qb->setParameter("word_to_search", $word_to_search);
        return  $this->queryService->paginationQuery($qb, $request);
    }



    public function findCmSku($request)
    {
        if ($request['searchValue']) {
            return  $this->filterData($request);
        }
        $qb = $this->getSelectColumn();
        $qb = $this->queryService->findAllByCountry($qb, "cm.dateCreditmemo", $request['typeStore'], "cm");
        return  $this->queryService->paginationQuery($qb, $request);
    }


    /**
     * FInd All CM Par SKU all site EUROPE for yesterday
     *
     * @param [integer] $storeId
     */
    public function findAllCmSkuEuroForYesterday($storeId = null)
    {

        $yesterday = new \DateTime('yesterday', new \DateTimezone('UTC'));

        $clause = "";

        if ($storeId) {
            $clause = " cm.store = $storeId AND ";
        } else {
            $clause = " cm.store  IN (3, 4, 14, 15, 16, 20, 22) AND";
        }
        $rawSql = "SELECT
                    DISTINCT  cm.id,
                    cm.store,
                    cm.sinceId,
                    cm.numCommande,
                    cm.numCreditmemo,
                    cm.nomClient,
                    cm.qty,
                    cm.rowTotalTax,
                    cm.tax,
                    cm.dateCreditmemo,
                    cm.sku,
                    cm.productId,
                    cm.name
                    FROM App\Entity\CmSku cm
                    WHERE   $clause cm.dateCreditmemo LIKE :yesterdayDate
                    ORDER BY cm.dateCreditmemo DESC
                    ";
        $stmt = $this->getEntityManager()->createQuery($rawSql);
        $stmt->setParameter('yesterdayDate', $yesterday->format('Y-m-d') . '%');
        return $stmt->execute();
    }

    public function findBetweenTwoDates($request, $storeId = null)
    {
        if ($request['searchValue']) {
            return  $this->filterData($request, $storeId);
        }
        // Convertissez les dates au format "YYYY-MM-DD"
        $minDate = date('Y-m-d', strtotime($request['minDate']));
        $maxDate = date('Y-m-d', strtotime($request['maxDate']));

        $qb = $this->getSelectColumn()
            ->where('SUBSTRING(cm.dateCreditmemo, 1, 10) BETWEEN :minDate AND :maxDate');
        $store = [
            3, 4, 14, 15, 16, 20, 21, 22
        ]; // Valeurs de base pour $store
        if ($request['except']) {
            $store = [
                3, 4, 14, 15, 16, 20, 22
            ];
        } elseif ($request['asiaSite']) {
            $store = [
                23, 24, 25
            ];
        }
        if ($storeId) {
            $qb->andWhere('cm.store = :storeId');
        } else {
            $qb->andWhere($qb->expr()->in('cm.store', $store));
        }
        $qb->orderBy("cm.dateCreditmemo", "DESC")
            ->setParameter('minDate', $minDate)
            ->setParameter('maxDate', $maxDate);
        if ($storeId) {
            $qb->setParameter('storeId', $storeId);     # code...
        }
        return  $this->queryService->paginationQuery($qb, $request);
    }



    public function findByStoreCmSku($storeId, $request)
    {
        if ($request['searchValue']) {
            return  $this->filterData($request, $storeId);
        }
        $qb = $this->getSelectColumn();
        $qb = $this->queryService->getDataFilterByStoreSelected($storeId, $qb, $request, 'cm');
        $qb->setParameter('storeID', $storeId);
        return  $this->queryService->paginationQuery($qb, $request);
    }
}
