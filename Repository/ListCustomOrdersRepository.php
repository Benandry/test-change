<?php

namespace App\Repository;

use App\Entity\ListCustomOrders;
use App\Service\QueryService;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method ListCustomOrders|null find($id, $lockMode = null, $lockVersion = null)
 * @method ListCustomOrders|null findOneBy(array $criteria, array $orderBy = null)
 * @method ListCustomOrders[]    findAll()
 * @method ListCustomOrders[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class ListCustomOrdersRepository extends ServiceEntityRepository
{
    private QueryService $queryService;
    public function __construct(ManagerRegistry $registry, QueryService $queryService)
    {
        parent::__construct($registry, ListCustomOrders::class);
        $this->queryService = $queryService;
    }


    private function getQueryBuilder()
    {
        $colmuns =
            [
                'c.id',
                'c.dateCommande',
                'c.numCommande',
                'c.numTracking',
                'c.taille',
                'c.sku',
                'c.envoi',
                'c.adress',
                'c.prenomClient',
                'c.nomClient',
                'c.imgBack',
                'c.imgFront',
                'c.sinceId',
                'c.store',
                'c.status',
            ];
        return  $this->createQueryBuilder('c')->select($colmuns);
    }

    public function findCustomOrder($request): array
    {
        if ($request['searchValue']) {
            return $this->filterData($request);
        }
        $qb = $this->getQueryBuilder();
        $qb = $this->queryService->findAllByCountry($qb, "c.dateCommande", $request['typeStore'], 'c');
        return  $this->queryService->paginationQuery($qb, $request);
    }



    private function filterData($request,  $storeId = null)
    {
        $word_to_search = "%" . $request['searchValue'] . "%";
        $colmuns =
            [
                'c.id',
                'c.dateCommande',
                'c.numCommande',
                'c.numTracking',
                'c.taille',
                'c.sku',
                'c.envoi',
                'c.adress',
                'c.prenomClient',
                'c.nomClient',
                'c.imgBack',
                'c.imgFront',
                'c.sinceId',
                'c.store',
                'c.status',
            ];

        $qb = $this->getQueryBuilder();
        $qb =  $this->queryService->search($qb, $colmuns, $storeId, $request['typeStore'], 'c.dateCommande');
        $qb->setParameter("word_to_search", $word_to_search);
        return  $this->queryService->paginationQuery($qb, $request);
    }

    /**
     *  Find all data in Custom Orders between two dates
     */
    public function  findBetweenTwoDates($request, $storeId = null)
    {
        if ($request['searchValue']) {
            return $storeId ? $this->filterData($request, $storeId) : $this->filterData($request);
        }
        $minDate = date('Y-m-d', strtotime($request['minDate']));
        $maxDate = date('Y-m-d', strtotime($request['maxDate']));
        $qb = $this->getQueryBuilder()
            ->where('SUBSTRING(c.dateCommande, 1, 10) BETWEEN :minDate AND :maxDate');

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
        $qb->orderBy("c.dateCommande", "DESC")
            ->setParameter('minDate', $minDate)
            ->setParameter('maxDate', $maxDate);
        if ($storeId) {
            $qb->setParameter('storeId', $storeId);     # code...
        }
        return  $this->queryService->paginationQuery($qb, $request);
    }


    public function findCustomOrderByStore($storeId, $request): array
    {
        if ($request['searchValue']) {
            return $this->filterData($request, $storeId);
        }
        $qb = $this->getQueryBuilder();
        $qb = $this->queryService->getStore($storeId, $qb, $request, 'c.dateCommande');
        $qb->setParameter('storeID', $storeId);
        return  $this->queryService->paginationQuery($qb, $request);
    }
}
