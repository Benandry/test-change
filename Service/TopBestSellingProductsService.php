<?php

namespace App\Service;

use App\Entity\CaSku;
use App\Repository\CaSkuRepository;
use App\Repository\SizeRepository;
use App\Repository\StoresRepository;

use App\Service\MorrisBarChartDataAsiaBuilder;
use App\Service\MorrisBarChartDataEuropeBuilder;
use App\Service\MorrisBarChartDataDBBuilder;

class TopBestSellingProductsService
{
  private $caSkuRepository;
  private $storesRepository;
  private $sizeRepository;
  private $morrisBarChartDataAsiaBuilder;
  private $morrisBarChartDataEuropeBuilder;
  private $morrisBarChartDataDBBuilder;
  private $morrisBarChartDataUSBuilder;

  public function __construct(
    CaSkuRepository $caSkuRepository,
    StoresRepository $storesRepository,
    SizeRepository $sizeRepository,
    MorrisBarChartDataAsiaBuilder $morrisDataAsiaBuilder,
    MorrisBarChartDataEuropeBuilder $morrisDataEuropeBuilder,
    MorrisBarChartDataDBBuilder $morrisDataDBBuilder,
    MorrisBarChartDataUSBuilder $morrisDataUSBuilder
  ) {
    $this->caSkuRepository = $caSkuRepository;
    $this->storesRepository = $storesRepository;
    $this->sizeRepository = $sizeRepository;
    $this->morrisBarChartDataAsiaBuilder = $morrisDataAsiaBuilder;
    $this->morrisBarChartDataEuropeBuilder = $morrisDataEuropeBuilder;
    $this->morrisBarChartDataDBBuilder = $morrisDataDBBuilder;
    $this->morrisBarChartDataUSBuilder = $morrisDataUSBuilder;
  }

  public function getTopBestSellingProductsByStore()
  {
    $stores = $this->storesRepository->findAll();

    $morrisBarChartDataAsia = [];
    $morrisBarChartDataEurope = [];
    $morrisBarChartDataDB = [];

    foreach ($stores as $store) {
      $storeId = $store->getId();
      $storeName = $store->getName();

      if (in_array(strtolower($storeName), ['row', 'ru', 'jp'])) {
        $morrisBarChartDataAsia[] = $this->morrisBarChartDataAsiaBuilder->buildData($storeId, $storeName);
      }

      if (in_array(strtolower($storeName), ['fr', 'en', 'es', 'de', 'it', 'nl', 'uk', 'ch'])) {
        $morrisBarChartDataEurope[] = $this->morrisBarChartDataEuropeBuilder->buildData($storeId, $storeName);
      }

      if (strtolower($storeName) === 'db') {
        $morrisBarChartDataDB[] = $this->morrisBarChartDataDBBuilder->buildData($storeId, $storeName);
      }

      if (strtolower($storeName) === 'us') {
        $morrisBarChartDataUS[] = $this->morrisBarChartDataUSBuilder->buildData($storeId, $storeName);
      }
    }

    // dd('morrisBarChartDataAsia', $morrisBarChartDataAsia, 'morrisBarChartDataEurope', $morrisBarChartDataEurope);

    return [
      'morrisBarChartDataAsia' => $morrisBarChartDataAsia,
      'morrisBarChartDataEurope' => $morrisBarChartDataEurope,
      'morrisBarChartDataDB' => $morrisBarChartDataDB,
      'morrisBarChartDataUS' => $morrisBarChartDataUS,
    ];
  }
}
