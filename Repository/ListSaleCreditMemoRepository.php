<?php

namespace App\Repository;

use App\Entity\ListSaleCreditMemo;
use App\Entity\Stores;
use App\Service\QueryService;
use DateTime;
use DateTimeZone;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method ListSaleCreditMemo|null find($id, $lockMode = null, $lockVersion = null)
 * @method ListSaleCreditMemo|null findOneBy(array $criteria, array $orderBy = null)
 * @method ListSaleCreditMemo[]    findAll()
 * @method ListSaleCreditMemo[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class ListSaleCreditMemoRepository extends ServiceEntityRepository
{
    private QueryService $queryService;
    public function __construct(ManagerRegistry $registry, QueryService $queryService)
    {
        parent::__construct($registry, ListSaleCreditMemo::class);
        $this->queryService = $queryService;
    }
    private function qbBase()
    {
        $columns = [
            "cm.id",
            "cm.numCommande",
            "cm.numCreditmemo",
            "cm.dateCreditmemo",
            "cm.status",
            "cm.store",
            "cm.dateCommande",
            "cm.nomClient",
            "cm.emailClient",
            "cm.idClient",
            "cm.countryCode",
            "cm.company",
            "cm.subtotal",
            "cm.shipping",
            "cm.discount",
            "cm.sinceId",
            "cm.refundId",
            "cm.isVisible",
            'UPPER(s.name) store_name'
        ];
        return  $this->createQueryBuilder('cm')
            ->select($columns)
            ->where("cm.isVisible = 1")
            ->join(Stores::class, 's', 'WITH', 'cm.store = s.id');
    }


    // SEARCH WORD IN LISTS SALES CREDIT MEMO
    private function filterData($request, $storeId = null)
    {
        $columns = [
            "cm.id",
            "cm.numCommande",
            "cm.numCreditmemo",
            "cm.dateCreditmemo",
            "cm.status",
            "cm.store",
            "cm.dateCommande",
            "cm.nomClient",
            "cm.emailClient",
            "cm.idClient",
            "cm.countryCode",
            "cm.company",
            "cm.subtotal",
            "cm.shipping",
            "cm.discount",
            "cm.sinceId",
            "cm.refundId",
            "cm.isVisible",
            "s.name"
        ];

        $qb = $this->qbBase();
        $qb =  $this->queryService->createFilterForm($qb, $columns, $storeId, $request['typeStore'], 'cm', 1);
        $qb->setParameter("word_to_search", '%' . $request['searchValue'] . '%');
        return  $this->queryService->paginationQuery($qb, $request);
    }


    //ALL SALES CREDIT MEMO EURO

    public function findSalesCreditMemo($request)
    {
        if ($request['searchValue']) {
            return  $this->filterData($request);
        }
        $qb = $this->qbBase();
        $qb = $this->queryService->findAllByCountry($qb, "cm.dateCreditmemo", $request['typeStore'], "cm");
        return  $this->queryService->paginationQuery($qb, $request);
    }

    public function findSalesCreditMemoByStore($param, $request)
    {
        if ($request['searchValue']) {
            return  $this->filterData($request, $param);
        }
        $qb = $this->qbBase();
        $qb = $this->queryService->getDataFilterByStoreSelected($param, $qb, $request, 'cm');
        $qb->setParameter('storeID', $param);
        return  $this->queryService->paginationQuery($qb, $request);
    }

    public function findBetweenTwoDates($request, $storeId = null)
    {
        if ($request['searchValue']) {
            return  $storeId ?  $this->filterData($request, $storeId) : $this->filterData($request);
        }
        $minDate = date('Y-m-d', strtotime($request['minDate']));
        $maxDate = date('Y-m-d', strtotime($request['maxDate']));
        $query = $this->qbBase()
            ->andWhere('SUBSTRING(cm.dateCreditmemo, 1, 10) BETWEEN :minDate AND :maxDate');

        $store = [3, 4, 14, 15, 16, 20, 21, 22, 26]; // Valeurs de base pour $store
        if ($request['except']) {
            $store = [3, 4, 14, 15, 16, 20, 22, 26];
        } elseif ($request['asiaSite']) {
            $store = [23, 24, 25];
        }
        if ($storeId) {
            $query->andWhere('cm.store = :storeId')
                ->setParameter('storeId', $storeId);
        } else {
            $query->andWhere($query->expr()->in('cm.store', $store));
        }
        $query->orderBy("cm.dateCreditmemo", "DESC")
            ->setParameter('minDate', $minDate)
            ->setParameter('maxDate', $maxDate);
        return  $this->queryService->paginationQuery($query, $request);
    }


    public function getYesterdayTotalSubtotalCreditMemos($stores)
    {
        $yesterday = new \DateTime('yesterday', new \DateTimezone('UTC'));

        $queryBuilder = $this->createQueryBuilder('lcm');
        $queryBuilder
            ->select('SUM(lcm.subtotal) AS totalSubtotalCreditMemos')
            ->where('lcm.dateCreditmemo LIKE :yesterday');

        if (is_array($stores)) {
            $storesString = implode(',', $stores);
            $queryBuilder->andWhere("lcm.store IN ({$storesString})");
        } else {
            $queryBuilder->andWhere('lcm.store = :store')
                ->setParameter('store', $stores);
        }

        $queryBuilder->setParameter('yesterday', $yesterday->format('Y-m-d') . '%');

        return  $queryBuilder->getQuery()->getSingleScalarResult();
    }
}
