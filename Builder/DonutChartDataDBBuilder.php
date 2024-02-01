<?php

namespace App\Builder;

use App\Repository\CmFactureRepository;
use App\Repository\CaFactureRepository;


class DonutChartDataDBBuilder implements DonutChartDataBuilderInterface
{
  private $donutChartDataBuilder;
  private $caFactureRepository;
  private $cmFactureRepository;
  private $region;

  public function __construct(DonutChartDataBuilder $donutChartDataBuilder, CaFactureRepository $caFactureRepository, CmFactureRepository $cmFactureRepository, string $region)
  {
    $this->donutChartDataBuilder = $donutChartDataBuilder;
    $this->caFactureRepository = $caFactureRepository;
    $this->cmFactureRepository = $cmFactureRepository;
    $this->region = $region;
  }

  public function buildData($region): array
  {
    $yesterdayTotalSubtotalCaFactureForDB =  $this->caFactureRepository->getYesterdayTotalSubtotalCaFacture($region);
    $yesterdayTotalSubtotalCmFactureForDB =  $this->cmFactureRepository->getYesterdayTotalSubtotalCreditMemos($region);

    return $this->donutChartDataBuilder
      ->setRegion($this->region)
      ->setRevenueValue($yesterdayTotalSubtotalCaFactureForDB[0]["grandTotalSubtotalCaFacture"])
      ->setCreditMemosValue($yesterdayTotalSubtotalCmFactureForDB)
      ->buildData();
  }
}
