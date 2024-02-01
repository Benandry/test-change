<?php

namespace App\Repository;

use App\Entity\ListSalesInvoices;
use App\Entity\Stores;
use App\Service\QueryService;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\QueryBuilder;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method ListSalesInvoices|null find($id, $lockMode = null, $lockVersion = null)
 * @method ListSalesInvoices|null findOneBy(array $criteria, array $orderBy = null)
 * @method ListSalesInvoices[]    findAll()
 * @method ListSalesInvoices[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class ListSalesInvoicesRepository extends ServiceEntityRepository
{
    private QueryService $queryService;
    public function __construct(ManagerRegistry $registry, QueryService $queryService)
    {
        parent::__construct($registry, ListSalesInvoices::class);
        $this->queryService = $queryService;
    }


    private function  getSelectColumn(): QueryBuilder
    {
        $columns = [
            "c.id",
            "c.sinceId",
            "c.numCommande",
            "c.numFacture",
            "c.store",
            "c.dateCommande",
            "c.dateFacture",
            "c.nomClient",
            "c.emailClient",
            "c.idClient",
            "c.countryCode",
            "c.company",
            "c.subtotal",
            "c.shipping",
            "c.discount",
            "c.Status",
            "c.isVisible",
            'UPPER(s.name) store_name'
        ];
        return  $this->createQueryBuilder('c')
            ->select($columns)
            ->where("c.isVisible = 1")
            ->join(Stores::class, 's', 'WITH', 'c.store = s.id');
    }

    public function findSalesInvoice($request)
    {
        if ($request['searchValue']) {
            return $this->filterData($request);
        }
        $qb = $this->getSelectColumn();
        $qb = $this->queryService->findAllByCountry($qb, "c.dateFacture", $request['typeStore'], 'c', 1);
        return $this->queryService->paginationQuery($qb, $request);
    }

    private function filterData($request, $storeId = null): array
    {
        $columns = [
            "c.id",
            "c.sinceId",
            "c.numCommande",
            "c.numFacture",
            "c.store",
            "c.dateCommande",
            "c.dateFacture",
            "c.nomClient",
            "c.emailClient",
            "c.idClient",
            "c.countryCode",
            "c.company",
            "c.subtotal",
            "c.shipping",
            "c.discount",
            "c.Status",
            "c.isVisible",
            "s.name"
        ];
        $qb = $this->getSelectColumn();
        $qb =  $this->queryService->createFilterForm($qb, $columns, $storeId, $request['typeStore'], 'c', 1);
        $qb->setParameter("word_to_search", "%" . $request['searchValue'] . "%");
        return  $this->queryService->paginationQuery($qb, $request);
    }


    public function findBetweenTwoDates($request, $storeId = null): array
    {
        if ($request['searchValue']) {
            return $storeId ? $this->filterData($request, $storeId) : $this->filterData($request);
        }
        $qb = $this->getSelectColumn();
        $smt = $this->queryService->getBetweenDate($qb, $storeId, $request, 'c.dateFacture', 1);
        return  $this->queryService->paginationQuery($smt, $request);
    }


    public function findSalesInvoiceByStore($param, $request)
    {
        if ($request['searchValue']) {
            return $this->filterData($request, $param);
        }
        $qb =  $this->getSelectColumn();
        $qb = $this->queryService->getStore($param, $qb, $request, 'c.dateFacture');
        $qb->setParameter('storeID', $param);
        return  $this->queryService->paginationQuery($qb, $request);
    }


    public function findBySalesInvoices($date, $storeId = null)
    {
        $query = $this->createQueryBuilder('l')
            ->andWhere('l.isVisible = 1')
            ->andWhere('l.dateFacture > :dateFacture')
            ->setParameter('dateFacture', $date);
        if ($storeId) {
            $query->andWhere('l.store = :store')
                ->setParameter('store', $storeId);
        }
        return $query->getQuery()->getArrayResult();
    }

    public function findBySalesInvoicesObjet($date, $storeId = null)
    {
        $query = $this->createQueryBuilder('l')
            ->andWhere('l.isVisible = 1')
            ->andWhere('l.dateFacture > :dateFacture')
            ->setParameter('dateFacture', $date);
        if ($storeId) {
            $query->andWhere('l.store = :store')
                ->setParameter('store', $storeId);
        }
        return $query->getQuery()->getResult();
    }


    public function getYesterdayTotalSubtotalSalesInvoices($stores)
    {

        $yesterday = new \DateTime('yesterday', new \DateTimezone('UTC'));

        $queryBuilder = $this->createQueryBuilder('lsi');
        $queryBuilder
            ->select('SUM(lsi.subtotal) AS totalSubtotalSalesInvoices')
            ->where('lsi.dateFacture LIKE :yesterday');

        if (is_array($stores)) {
            $storesString = implode(',', $stores);
            $queryBuilder->andWhere("lsi.store IN ({$storesString})");
        } else {
            $queryBuilder->andWhere('lsi.store = :store')
                ->setParameter('store', $stores);
        }

        $queryBuilder->setParameter('yesterday', $yesterday->format('Y-m-d') . '%');
        // $queryBuilder->setParameter('yesterday', $yesterday->format('2023-03-22') . '%');

        $result = $queryBuilder->getQuery()->getSingleScalarResult();

        return $result;
    }
}
