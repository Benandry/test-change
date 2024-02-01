<?php

namespace App\Repository;

use App\Entity\UfcOrders;
use App\Service\QueryService;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\OptimisticLockException;
use Doctrine\ORM\ORMException;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<UfcOrders>
 *
 * @method UfcOrders|null find($id, $lockMode = null, $lockVersion = null)
 * @method UfcOrders|null findOneBy(array $criteria, array $orderBy = null)
 * @method UfcOrders[]    findAll()
 * @method UfcOrders[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class UfcOrdersRepository extends ServiceEntityRepository
{
    private QueryService $queryService;
    public function __construct(ManagerRegistry $registry, QueryService $queryService)
    {
        parent::__construct($registry, UfcOrders::class);
        $this->queryService = $queryService;
    }

    /**
     * @throws ORMException
     * @throws OptimisticLockException
     */
    public function add(UfcOrders $entity, bool $flush = true): void
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
    public function remove(UfcOrders $entity, bool $flush = true): void
    {
        $this->_em->remove($entity);
        if ($flush) {
            $this->_em->flush();
        }
    }



    private function getSelectColumn()
    {
        $columns = [
            'c.id',
            'c.store',
            'c.invoice_id',
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
        return $this->createQueryBuilder('c')->select($columns)->where('c.isVisible = 1');
    }

    public function findByStoreOrder($param, $request): array
    {
        if ($request['searchValue']) {
            return  $this->filterData($request, $param);
        }

        $qb = $this->queryService->getStore($param, $this->getSelectColumn(), $request, 'c.dateFacture');
        $qb->setParameter('storeID', $param);
        return  $this->queryService->paginationQuery($qb, $request);
    }


    private function filterData($request, $storeId = null)
    {

        $word_to_search = "%" . $request['searchValue'] . "%";
        $columns = [
            'c.id',
            'c.store',
            'c.invoice_id',
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

        $qb = $this->getSelectColumn();
        $qb =  $this->queryService->createFilterForm($qb, $columns, $storeId, $request['typeStore'], 'c');
        $qb->setParameter("word_to_search", $word_to_search);
        return  $this->queryService->paginationQuery($qb, $request);
    }

    public function findUfcOrder($request)
    {
        if ($request['searchValue']) {
            return  $this->filterData($request);
        }
        $qb  = $this->getSelectColumn();
        $qb = $this->queryService->findAllByCountry($qb, "c.dateFacture", $request['typeStore'], 'c', 1);
        return $this->queryService->paginationQuery($qb, $request);
    }

    public function findOrdertweenTwoDates($request, $storeId = null): array
    {
        if ($request['searchValue']) {
            return  $storeId ? $this->filterData($request, $storeId) :  $this->filterData($request);
        }

        $qb = $this->getSelectColumn();
        $qb = $this->queryService->getBetweenDate($qb, $storeId, $request, 'c.dateFacture', 1);
        return  $this->queryService->paginationQuery($qb, $request);
    }
}
