<?php

namespace App\Service;

use App\Repository\CaSkuRepository;

class MorrisBarChartDataUSBuilder implements MorrisBarChartDataBuilderInterface
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

    $top10BestSellingCaSkus = $this->caSkuRepository->findAllCaSkusInTheLast30DaysByStore($storeId, 10);

    $topTenBestSellingProducts = $this->topSKUService->getTopSKUs($top10BestSellingCaSkus, $storeId);

    $formattedStore['topTenBestSellingProducts'] = $topTenBestSellingProducts;

    return $formattedStore;
  }
}
