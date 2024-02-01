<?php

namespace App\Builder;

use App\Entity\CaFacture;
use App\Repository\CmFactureRepository;
use App\Repository\CaFactureRepository;


class DonutChartDataEuroBuilder implements DonutChartDataBuilderInterface
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
    $yesterdayTotalSubtotalCaFactureForEuro =  $this->caFactureRepository->getYesterdayTotalSubtotalCaFacture($stores);
    $yesterdayTotalSubtotalCmFactureForEuro =  $this->cmFactureRepository->getYesterdayTotalSubtotalCreditMemos($stores);

    return $this->donutChartDataBuilder
      ->setRegion($this->region)
      ->setRevenueValue($yesterdayTotalSubtotalCaFactureForEuro[0]["grandTotalSubtotalCaFacture"])
      ->setCreditMemosValue($yesterdayTotalSubtotalCmFactureForEuro)
      ->buildData();
  }
}
