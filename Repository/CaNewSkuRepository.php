<?php

namespace App\Repository;

use App\Entity\CaNewSku;
use App\Service\QueryService;
use DateTime;
use DateTimeZone;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\OptimisticLockException;
use Doctrine\ORM\ORMException;
use Doctrine\ORM\QueryBuilder;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<CaNewSku>
 *
 * @method CaNewSku|null find($id, $lockMode = null, $lockVersion = null)
 * @method CaNewSku|null findOneBy(array $criteria, array $orderBy = null)
 * @method CaNewSku[]    findAll()
 * @method CaNewSku[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class CaNewSkuRepository extends ServiceEntityRepository
{
    private QueryService $queryService;
    public function __construct(ManagerRegistry $registry, QueryService $queryService)
    {
        parent::__construct($registry, CaNewSku::class);
        $this->queryService  = $queryService;
    }

    /**
     * @throws ORMException
     * @throws OptimisticLockException
     */
    public function add(CaNewSku $entity, bool $flush = true): void
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
    public function remove(CaNewSku $entity, bool $flush = true): void
    {
        $this->_em->remove($entity);
        if ($flush) {
            $this->_em->flush();
        }
    }

    private function getColumns(): array
    {
        return [
            'c.productId',
            'c.numCommande',
            'c.store',
            'c.dateFacture',
            'c.numFacture',
            'c.typeDeBien',
            'c.sku',
            'c.name',
            'c.totalQtyOrder',
            'c.priceHt',
            'c.montantHt',
            'c.montantTTC',
            'c.company',
            'c.companyFacturaation',
            'c.nomClient',
            'c.paysLivraison',
            'c.sinceId',
            'c.isVisible'
        ];
    }

    private function  getSelectColumn(): QueryBuilder
    {
        return  $this->createQueryBuilder('c')->select($this->getColumns())->where("c.isVisible = 1");
    }


    public function filterData($request, $param = null): array
    {
        $columns = [
            'c.productId',
            'c.numCommande',
            'c.store',
            'c.dateFacture',
            'c.numFacture',
            'c.typeDeBien',
            'c.sku',
            'c.name',
            'c.totalQtyOrder',
            'c.priceHt',
            'c.montantHt',
            'c.montantTTC',
            'c.company',
            'c.companyFacturaation',
            'c.nomClient',
            'c.paysLivraison',
            'c.sinceId',
            'c.isVisible'
        ];
        $qb = $this->getSelectColumn();
        $qb =  $this->queryService->createFilterForm($qb, $columns, $param, $request['typeStore'], 'c');
        $qb->setParameter("word_to_search", "%" . $request['searchValue'] . "%");
        return  $this->queryService->paginationQuery($qb, $request);
    }



    public function findByStoreCaNewSku($storeId, $request): array
    {
        if ($request['searchValue']) {
            return  $this->filterData($request, $storeId);
        }
        $qb = $this->getSelectColumn();
        $qb = $this->queryService->getDataFilterByStoreSelected($storeId, $qb, $request, 'c');
        $qb->setParameter('storeID', $storeId);
        return  $this->queryService->paginationQuery($qb, $request);
    }
    public function findCaNewSku($request): array
    {
        if ($request['searchValue']) {
            return  $this->filterData($request);
        }
        $qb = $this->getSelectColumn();
        $qb = $this->queryService->findAllByCountry($qb, "c.dateFacture", $request['typeStore'], "c");
        return  $this->queryService->paginationQuery($qb, $request);
    }

    public function findDataBetweenDates($request, $store = null)
    {
        if ($request['searchValue']) {
            return $store ? $this->filterData($request, $store) : $this->filterData($request);
        }
        $qb = $this->getSelectColumn();
        $qb = $this->queryService->getBetweenDate($qb, $store, $request, 'c.dateFacture');
        return  $this->queryService->paginationQuery($qb, $request);
    }
}
