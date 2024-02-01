<?php

namespace App\Service;

use App\Builder\DonutChartDataAsiaBuilder;
use App\Builder\DonutChartDataDBBuilder;
use App\Builder\DonutChartDataEuroBuilder;
use App\Builder\DonutChartDataUSBuilder;
use App\Repository\StoresRepository;

class DonutChartDataService
{
  private $storesRepository;
  private $asiaBuilder;
  private $euroBuilder;
  private $dbBuilder;
  private $usBuilder;

  public function __construct(
    StoresRepository $storesRepository,
    DonutChartDataAsiaBuilder $asiaBuilder,
    DonutChartDataEuroBuilder $euroBuilder,
    DonutChartDataDBBuilder $dbBuilder,
    DonutChartDataUSBuilder $usBuilder
  ) {
    $this->storesRepository = $storesRepository;
    $this->asiaBuilder = $asiaBuilder;
    $this->euroBuilder = $euroBuilder;
    $this->dbBuilder = $dbBuilder;
    $this->usBuilder = $usBuilder;
  }

  public function getTotalOfRevenuesAndCreditMemosForEachRegion()
  {
    $stores = $this->storesRepository->findAll();

    $asiaDonutChartData = [];
    $euroDonutChartData = [];
    $dbDonutChartData = [];
    $usDonutChartData = [];

    $asiaStoresIds = [];
    $euroStoresIds = [];
    $dbStoreId = 0;
    $usStoreId = 0;

    foreach ($stores as $store) {
      $storeId = $store->getId();
      $storeName = $store->getName();

      if (in_array(strtolower($storeName), ['row', 'ru', 'jp'])) {
        $asiaStoresIds[] = $storeId;
      }

      if (in_array(strtolower($storeName), ['fr', 'en', 'es', 'de', 'it', 'nl', 'uk', 'ch'])) {
        $euroStoresIds[] = $storeId;
      }

      if (strtolower($storeName) === 'db') {
        $dbStoreId = $storeId;
      }

      if (strtolower($storeName) === 'us') {
        $usStoreId = $storeId;
      }
    }

    $euroDonutChartData[] = $this->euroBuilder->buildData(implode(',', $euroStoresIds));
    $asiaDonutChartData[] = $this->asiaBuilder->buildData(implode(',', $asiaStoresIds));
    $dbDonutChartData[] = $this->dbBuilder->buildData($dbStoreId);
    $usDonutChartData[] = $this->usBuilder->buildData($usStoreId);

    // dd($euroDonutChartData, $asiaDonutChartData, $dbDonutChartData, $usDonutChartData);

    $result = [
      'asiaDonutChartData' => $asiaDonutChartData,
      'euroDonutChartData' => $euroDonutChartData,
      'dbDonutChartData' => $dbDonutChartData,
      'usDonutChartData' => $usDonutChartData,
    ];

    return $result;
  }
}
