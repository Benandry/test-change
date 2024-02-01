<?php

namespace App\Repository;


use App\Entity\ListTvaInvoices;
use App\Entity\Stores;
use App\Service\QueryService;
use DateTime;
use DateTimeZone;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\Query;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method ListTvaInvoices|null find($id, $lockMode = null, $lockVersion = null)
 * @method ListTvaInvoices|null findOneBy(array $criteria, array $orderBy = null)
 * @method ListTvaInvoices[]    findAll()
 * @method ListTvaInvoices[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class ListTvaInvoicesRepository extends ServiceEntityRepository
{
    private QueryService $queryService;
    public function __construct(ManagerRegistry $registry, QueryService $queryService)
    {
        parent::__construct($registry, ListTvaInvoices::class);
        $this->queryService = $queryService;
    }
    private function getSelectColumn()
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
            "c.isVisible",
            'UPPER(s.name) store_name'
        ];
        return  $this->createQueryBuilder('c')
            ->select($columns)
            ->where("c.isVisible = 1")
            ->join(Stores::class, 's', 'WITH', 'c.store = s.id');
    }

    public function findByStoreTvaInvoices($param, $request): array
    {
        if ($request['searchValue']) {
            return $this->filterData($request, $param);
        }
        $qb =  $this->getSelectColumn();
        $qb = $this->queryService->getStore($param, $qb, $request, 'c.dateFacture');
        $qb->setParameter('storeID', $param);
        return  $this->queryService->paginationQuery($qb, $request);
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
            "c.isVisible",
            's.name'
        ];
        $qb = $this->getSelectColumn();
        $qb =  $this->queryService->createFilterForm($qb, $columns, $param, $request['typeStore'], 'c', 1);
        $qb->setParameter("word_to_search", "%" . $request['searchValue'] . "%");
        return $this->queryService->paginationQuery($qb, $request);
    }



    public function  findTvaInvoices($request): array
    {
        if ($request['searchValue']) {
            return $this->filterData($request);
        }
        $qb = $this->getSelectColumn();
        $qb = $this->queryService->findAllByCountry($qb, "c.dateFacture", $request['typeStore'], 'c', 1);
        return $this->queryService->paginationQuery($qb, $request);
    }

    public function findBetweenTwoDates($request, $param = null): array
    {
        if ($request['searchValue']) {
            return $param ? $this->filterData($request, $param) : $this->filterData($request);
        }
        $qb = $this->getSelectColumn();
        $qb = $this->queryService->getBetweenDate($qb, $param, $request, 'c.dateFacture', 1);
        return  $this->queryService->paginationQuery($qb, $request);
    }


    /**
     * FInd by num commande to edit in LIST TVA INVOICES
     *
     * @param [type] $value
     * @return array
     */
    public function findByNumCommand($value): array
    {
        $value = "%-$value-%";
        $queryBuilder =  $this->createQueryBuilder('t')
            ->where('t.numFacture like :val')
            ->setParameter('val', $value)
            ->getQuery()
            ->getResult();
        return $queryBuilder ?  $queryBuilder : [];
    }
}
