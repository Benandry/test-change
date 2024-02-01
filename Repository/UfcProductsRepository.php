<?php

namespace App\Repository;

use App\Entity\UfcProducts;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method UfcProducts|null find($id, $lockMode = null, $lockVersion = null)
 * @method UfcProducts|null findOneBy(array $criteria, array $orderBy = null)
 * @method UfcProducts[]    findAll()
 * @method UfcProducts[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class UfcProductsRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, UfcProducts::class);
    }

}
