<?php

namespace App\Builder;

use App\Repository\CaFactureRepository;
use App\Repository\CmFactureRepository;

class DonutChartDataAsiaBuilder implements DonutChartDataBuilderInterface
{
  private $donutChartDataBuilder;
  private $caFactureRepository;
  private $cmFactureRepository;
  private $region;

  public function __construct(
    DonutChartDataBuilder $donutChartDataBuilder,
    CaFactureRepository $caFactureRepository,
    CmFactureRepository $cmFactureRepository,
    string $region
  ) {
    $this->donutChartDataBuilder = $donutChartDataBuilder;
    $this->caFactureRepository = $caFactureRepository;
    $this->cmFactureRepository = $cmFactureRepository;
    $this->region = $region;
  }

  public function buildData($stores): array
  {
    $yesterdayTotalSubtotalCaFactureForAsia =  $this->caFactureRepository->getYesterdayTotalSubtotalCaFacture($stores);
    $yesterdayTotalSubtotalCmFactureForAsia =  $this->cmFactureRepository->getYesterdayTotalSubtotalCreditMemos($stores);

    return $this->donutChartDataBuilder
      ->setRegion($this->region)
      ->setRevenueValue($yesterdayTotalSubtotalCaFactureForAsia[0]["grandTotalSubtotalCaFacture"])
      ->setCreditMemosValue($yesterdayTotalSubtotalCmFactureForAsia)
      ->buildData();
  }
}
