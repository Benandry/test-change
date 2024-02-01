<?php

namespace App\Repository;

use App\Entity\ListUfcOrders;
use App\Service\QueryService;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\QueryBuilder;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method ListUfcOrders|null find($id, $lockMode = null, $lockVersion = null)
 * @method ListUfcOrders|null findOneBy(array $criteria, array $orderBy = null)
 * @method ListUfcOrders[]    findAll()
 * @method ListUfcOrders[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class ListUfcOrdersRepository extends ServiceEntityRepository
{
    private QueryService $queryService;
    public function __construct(ManagerRegistry $registry, QueryService $queryService)
    {
        parent::__construct($registry, ListUfcOrders::class);
        $this->queryService = $queryService;
    }


    private function getSelectColumn(): QueryBuilder
    {
        $columns =  [
            'c.id',
            'c.dateCommande',
            'c.numCommande',
            'c.numTracking',
            'c.type',
            'c.taille',
            'c.couleur',
            'c.flocage',
            'c.nom',
            'c.numCaractere',
            'c.pays',
            'c.sku',
            'c.status',
            'c.envoi',
            'c.adress',
            'c.prenomClient',
            'c.nomClient',
            'c.imgBack',
            'c.imgFront',
            'c.sinceId',
            'c.store',
        ];
        return  $this->createQueryBuilder('c')->select($columns);
    }

    public function findUfcOrderALl($request): array
    {
        if ($request['searchValue']) {
            return $this->filterData($request);
        }
        $qb = $this->getSelectColumn();
        $qb = $this->queryService->findAllByCountry($qb, "c.dateCommande", $request['typeStore'], 'c', 1);
        return $this->queryService->paginationQuery($qb, $request);
    }
    public function filterData($request, $storeId = null)
    {
        $columns =  [
            'c.id',
            'c.dateCommande',
            'c.numCommande',
            'c.numTracking',
            'c.type',
            'c.taille',
            'c.couleur',
            'c.flocage',
            'c.nom',
            'c.numCaractere',
            'c.pays',
            'c.sku',
            'c.status',
            'c.envoi',
            'c.adress',
            'c.prenomClient',
            'c.nomClient',
            'c.imgBack',
            'c.imgFront',
            'c.sinceId',
            'c.store',
        ];

        $qb = $this->getSelectColumn();
        $qb =  $this->queryService->search($qb, $columns, $storeId, $request['typeStore'], 'c.dateCommande');
        $qb->setParameter("word_to_search", "%" . $request['searchValue'] . "%");
        return  $this->queryService->paginationQuery($qb, $request);
    }

    public function findUfcOrderByStore($storeId, $request)
    {
        if ($request['searchValue']) {
            return $this->filterData($request, $storeId);
        }

        $qb =  $this->getSelectColumn();
        $qb = $this->queryService->getStore($storeId, $qb, $request, 'c.dateCommande');
        $qb->setParameter('storeID', $storeId);
        return  $this->queryService->paginationQuery($qb, $request);
    }

    public function findBetweenTwoDates($request, $storeId = null): array
    {
        if ($request['searchValue']) {
            return $this->filterData($request, $storeId);
        }
        $qb = $this->getSelectColumn();
        $qb = $this->queryService->getBetweenDate($qb, $storeId, $request, 'c.dateCommande', 1);
        return  $this->queryService->paginationQuery($qb, $request);
    }
}
