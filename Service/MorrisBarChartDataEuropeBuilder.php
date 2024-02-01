<?php

namespace App\Service;


use App\Repository\CaSkuRepository;

class MorrisBarChartDataEuropeBuilder implements MorrisBarChartDataBuilderInterface

{
  private $caSkuRepository;
  private $topSKUService;

  public function __construct(CaSkuRepository $caSkuRepository, TopSKUService $topSKUService)
  {
    $this->caSkuRepository = $caSkuRepository;
    $this->topSKUService = $topSKUService;
  }

  public function buildData(int $storeId, string $storeName): array
  {

    $formattedStore = ['store' => $storeName];

    $top3BestSellingCaSkus = $this->caSkuRepository->findAllCaSkusInTheLast30DaysByStore($storeId, 3);

    $topThreeBestSellingProducts = $this->topSKUService->getTopSKUs($top3BestSellingCaSkus, $storeId);

    $formattedStore['topThreeBestSellingProducts'] = $topThreeBestSellingProducts;

    return $formattedStore;
  }
}
