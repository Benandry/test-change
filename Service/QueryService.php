<?php

namespace App\Service;

use DateTime;
use DateTimeZone;
use Doctrine\ORM\QueryBuilder;

class QueryService
{

    /**
     * Verifier les stores ID
     *
     * @param [type] $storeIds
     * @param [type] $qb
     * @return QueryBuilder
     */
    public function queryStoreId($storeIds, $qb, $c): QueryBuilder
    {
        if (is_array($storeIds)  && !empty($storeIds)) {
            return  $qb->andWhere("$c.store IN (:storeID) ");
        } else {
            return  $qb->andWhere("$c.store  = :storeID");
        }
    }

    /**
     * Get Type store
     *
     * @param [type] $toreIds
     * @param [type] $typeStore
     * @param [type] $qb
     * @return QueryBuilder
     */
    public function getTypeStore($typeStore, $qb, $c, $showDb): QueryBuilder
    {
        switch ($typeStore) {
            case 1:
                $qb->andWhere("$c.store  IN (:storeIds)")
                    ->setParameter('storeIds', [23, 24, 25]);
                break;
            case 2:
                $qb->andWhere("$c.store  IN (:storeIds)");
                ($showDb) ? $qb->setParameter('storeIds', [3, 4, 14, 15, 16, 20, 22, 26])
                    : $qb->setParameter('storeIds', [3, 4, 14, 15, 16, 20, 22]);
                break;
            default:
                $qb->andWhere("$c.store  IN (:storeIds)");
                ($showDb) ? $qb->setParameter('storeIds', [3, 4, 14, 15, 16, 20, 21, 22, 26])
                    : $qb->setParameter('storeIds', [3, 4, 14, 15, 16, 20, 21, 22]);
                break;
        }

        return $qb;
    }

    /**
     * Create filter form query
     *
     * @param [type] $qb
     * @param [type] $columns
     * @param [type] $storeId
     * @param [type] $typeStore
     * @param [type] $c
     * @return  QueryBuilder
     */
    public function createFilterForm($qb, $columns, $storeId, $typeStore, $c, $showDb = null): QueryBuilder
    {
        $orX = $qb->expr()->orX();
        foreach ($columns as $column) {
            $orX->add($qb->expr()->like("$column", ":word_to_search"));
        }
        $orX->add($qb->expr()->like("s.name", ":word_to_search"));
        $qb->andWhere($orX);
        if ($storeId) {
            $qb = $this->queryStoreId($storeId, $qb, $c)->setParameter('storeID',  $storeId);
        } else {
            $qb = $this->getTypeStore($typeStore, $qb, $c, $showDb);
        }
        ($c == "cm") ?  $qb->orderBy("$c.dateCreditmemo", 'DESC') : $qb->orderBy("$c.dateFacture", 'DESC');

        return $qb;
    }

    /**
     * Get data selected store
     *
     * @param [type] $storeIds
     * @param [type] $qb
     * @param [type] $minDate
     * @param [type] $maxDate
     * @param [type] $c
     * @return QueryBuilder
     */
    public function getDataFilterByStoreSelected($storeIds, $qb, $request, $c): QueryBuilder
    {
        $minDate = $request['minDate'];
        $maxDate = $request['maxDate'];
        $qb = $this->queryStoreId($storeIds, $qb, $c);
        if ($minDate && $maxDate) {
            if ($c == "cm") {
                $qb->andWhere("SUBSTRING($c.dateCreditmemo, 1, 10) BETWEEN :minDate AND :maxDate")
                    ->orderBy("$c.dateCreditmemo", 'DESC');
            } else {
                $qb->andWhere("SUBSTRING($c.dateFacture, 1, 10) BETWEEN :minDate AND :maxDate")
                    ->orderBy("$c.dateFacture", 'DESC');
            }
        }

        if ($c == "cm") {
            $qb->orderBy("$c.dateCreditmemo", 'DESC');
        } else {
            $qb->orderBy("$c.dateFacture", 'DESC');
        }

        if ($minDate && $maxDate) {
            $minDate = date('Y-m-d', strtotime($minDate));
            $maxDate = date('Y-m-d', strtotime($maxDate));
            $qb->setParameter('minDate', $minDate)->setParameter('maxDate', $maxDate);
        }

        return $qb;
    }


    /**
     * Find store in Euro,Except UK , Asia
     *
     * @param [type] $qb
     * @param [type] $c
     * @param [type] $type
     * @param [type] $showDb
     * @return QueryBuilder
     */
    public function findStore($qb, $c, $type, $showDb = null): QueryBuilder
    {
        $datetime = new DateTime('now', new DateTimeZone('Europe/Paris'));
        $monthYearCurrent = $datetime->format('Y-m') . '%';

        $qb = $this->getTypeStore($type, $qb, $c, $showDb);
        if ($c == "cm") {
            $qb->andWhere("$c.dateCreditmemo LIKE :monthYearCurrent")->orderBy("$c.dateCreditmemo", 'DESC');
        } else {
            $qb->andWhere("$c.dateFacture LIKE :monthYearCurrent")->orderBy("$c.dateFacture", 'DESC');
        }

        $qb->setParameter('monthYearCurrent', $monthYearCurrent);
        return $qb;
    }


    /**
     * Make pagination for arrays result
     *
     * @param [type] $qb
     * @param [type] $start
     * @param [type] $length
     * @return Array
     */
    public function paginationQuery($qb, $request): array
    {
        $secondResult = $qb->getQuery()->getResult();
        $firstResult = $qb->setFirstResult($request['start'])
            ->setMaxResults($request['length'])
            ->getQuery()
            ->getResult();
        return [
            $firstResult,
            $secondResult,
        ];
    }

    public function getDataTwoDates($qb, $storeId, $typeStore, $minDate, $maxDate, $c, $showDb = null): QueryBuilder
    {

        if ($storeId) {
            $qb = $this->queryStoreId($storeId, $qb, $c);
            $qb->setParameter('storeID',  $storeId);
        } else {
            $qb = $this->getTypeStore($typeStore, $qb, $c, $showDb);
        }

        ($c == "cm") ? $qb->andWhere(" SUBSTRING($c.dateCreditmemo, 1, 10) BETWEEN :minDate AND :maxDate ")
            ->orderBy("$c.dateCreditmemo", 'DESC')
            :  $qb->andWhere("SUBSTRING($c.dateFacture, 1, 10) BETWEEN :minDate AND :maxDate")
            ->orderBy("$c.dateFacture", 'DESC');

        $qb->orderBy("c.dateFacture", "DESC")->setParameter('minDate', $minDate)->setParameter('maxDate', $maxDate);

        return $qb;
    }

    public function getStore($storeIds, $qb, $request, $dateColumn): QueryBuilder
    {
        $minDate = $request['minDate'];
        $maxDate = $request['maxDate'];
        $qb = $this->queryStoreId($storeIds, $qb, 'c');
        if ($minDate && $maxDate) {
            $qb->andWhere("SUBSTRING($dateColumn, 1, 10) BETWEEN :minDate AND :maxDate")
                ->setParameter('minDate', date('Y-m-d', strtotime($minDate)))
                ->setParameter('maxDate', date('Y-m-d', strtotime($maxDate)));
        }
        return $qb->orderBy($dateColumn, 'DESC');
    }

    public function search($qb, $columns, $storeId, $typeStore, $dateColumn, $showDb = null): QueryBuilder
    {
        $orX = $qb->expr()->orX();
        foreach ($columns as $column) {
            $orX->add($qb->expr()->like("$column", ":word_to_search"));
        }
        $qb->andWhere($orX);
        if ($storeId) {
            $qb = $this->queryStoreId($storeId, $qb, 'c')->setParameter('storeID',  $storeId);
        } else {
            $qb = $this->getTypeStore($typeStore, $qb, 'c', $showDb);
        }
        return $qb->orderBy($dateColumn, 'DESC');
    }

    public function findAllByCountry($qb, $dateColumn, $type, $c, $showDb = null): QueryBuilder
    {
        $datetime = new DateTime('now', new DateTimeZone('Europe/Paris'));

        return $this->getTypeStore($type, $qb, $c, $showDb)
            ->andWhere("$dateColumn LIKE :monthYearCurrent")
            ->orderBy($dateColumn, 'DESC')
            ->setParameter('monthYearCurrent', $datetime->format('Y-m') . '%');
    }

    public function getBetweenDate($qb, $storeId, $request, $dateColumn, $showDb = null): QueryBuilder
    {
        $minDate = $request['minDate'];
        $maxDate = $request['maxDate'];
        $typeStore = $request['typeStore'];
        if ($storeId) {
            $qb = $this->queryStoreId($storeId, $qb, 'c');
            $qb->setParameter('storeID',  $storeId);
        } else {
            $qb = $this->getTypeStore($typeStore, $qb, 'c', $showDb);
        }
        return  $qb->andWhere(" SUBSTRING($dateColumn, 1, 10) BETWEEN :minDate AND :maxDate ")
            ->orderBy($dateColumn, 'DESC')
            ->setParameter('minDate', date('Y-m-d', strtotime($minDate)))
            ->setParameter('maxDate', date('Y-m-d', strtotime($maxDate)));
    }
}
