<?php

namespace App\Repository;


use App\Entity\ListTvaCreditmemo;
use App\Entity\Stores;
use App\Service\QueryService;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\QueryBuilder;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method ListTvaCreditmemo|null find($id, $lockMode = null, $lockVersion = null)
 * @method ListTvaCreditmemo|null findOneBy(array $criteria, array $orderBy = null)
 * @method ListTvaCreditmemo[]    findAll()
 * @method ListTvaCreditmemo[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class ListTvaCreditmemoRepository extends ServiceEntityRepository
{
    private QueryService $queryService;

    public function __construct(ManagerRegistry $registry, QueryService $queryService)
    {
        parent::__construct($registry, ListTvaCreditmemo::class);
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
            "c.dateFacture",
            "c.countryCode",
            "c.company",
            "c.subtotal",
            "c.shipping",
            "c.discount",
            "c.compteComptable",
            "c.baseHtMulti",
            "c.baseHt20",
            "c.baseHt55",
            "c.baseHt21",
            "c.baseHt0",
            "c.baseHt6",
            "c.tvaMulti",
            "c.tva20",
            "c.tva55",
            "c.tva0",
            "c.tva21",
            "c.tva6",
            "c.tauxTaxe",
            "c.totalTtc",
            "c.taxeLivraison",
            "c.montantTaxe",
            "c.discountPercent",
            "c.storeCredit",
            "c.ecart",
            "c.refundId",
            "c.isVisible",
            'UPPER(s.name) store_name'
        ];
        return  $this->createQueryBuilder('c')
            ->select($columns)
            ->where("c.isVisible = 1")
            ->join(Stores::class, 's', 'WITH', 'c.store = s.id');
    }
    private function  filterData($request, $param = null): array
    {
        $columns = [
            "c.id",
            "c.sinceId",
            "c.numCommande",
            "c.numFacture",
            "c.store",
            "c.dateFacture",
            "c.countryCode",
            "c.company",
            "c.subtotal",
            "c.shipping",
            "c.discount",
            "c.compteComptable",
            "c.baseHtMulti",
            "c.baseHt20",
            "c.baseHt55",
            "c.baseHt21",
            "c.baseHt0",
            "c.baseHt6",
            "c.tvaMulti",
            "c.tva20",
            "c.tva55",
            "c.tva0",
            "c.tva21",
            "c.tva6",
            "c.tauxTaxe",
            "c.totalTtc",
            "c.taxeLivraison",
            "c.montantTaxe",
            "c.discountPercent",
            "c.storeCredit",
            "c.ecart",
            "c.refundId",
            "c.isVisible",
            "s.name"
        ];
        $qb = $this->getSelectColumn();
        $qb =  $this->queryService->createFilterForm($qb, $columns, $param, $request['typeStore'], 'c', 1);
        $qb->setParameter("word_to_search", "%" . $request['searchValue'] . "%");
        return $this->queryService->paginationQuery($qb, $request);
    }

    public function  findTvaCreditMemo($request): array
    {
        if ($request['searchValue']) {
            return $this->filterData($request);
        }
        $qb = $this->getSelectColumn();
        $qb = $this->queryService->findAllByCountry($qb, "c.dateFacture", $request['typeStore'], 'c', 1);
        return $this->queryService->paginationQuery($qb, $request);
    }

    public function findByStoreTvaCreditMemo($param, $request): array
    {
        if ($request['searchValue']) {
            return $this->filterData($request, $param);
        }
        $qb =  $this->getSelectColumn();
        $qb = $this->queryService->getStore($param, $qb, $request, 'c.dateFacture');
        $qb->setParameter('storeID', $param);
        return  $this->queryService->paginationQuery($qb, $request);
    }

    public function findBetweenTwoDates($request, $store = null): array
    {
        if ($request['searchValue']) {
            return $store ? $this->filterData($request, $store) : $this->filterData($request);
        }
        $qb = $this->getSelectColumn();
        $qb = $this->queryService->getBetweenDate($qb, $store, $request, 'c.dateFacture', 1);
        return  $this->queryService->paginationQuery($qb, $request);
    }
    /**
     * FInd by num commande to edit in LIST SALES CREDIT MEMO
     *
     * @param [type] $value
     * @return array
     */
    public function findByNumCommand($value): array
    {
        $value = "%-$value-%";
        $queryBuilder =  $this->createQueryBuilder('t')
            ->where('t.numCommande like :val')
            ->setParameter('val', $value)
            ->getQuery()
            ->getResult();
        return $queryBuilder ? $queryBuilder : [];
    }
}
