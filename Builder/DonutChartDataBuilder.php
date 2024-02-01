<?php

namespace App\Builder;

class DonutChartDataBuilder
{
  private $region;
  private $totalSubtotalCaFacture;
  private $totalSubtotalCmFacture;

  public function __construct(string $region, ?float $totalSubtotalCaFacture, ?float $totalSubtotalCmFacture)
  {
    $this->region = $region;
    $this->totalSubtotalCaFacture = $totalSubtotalCaFacture;
    $this->totalSubtotalCmFacture = $totalSubtotalCmFacture;
  }

  public function setRegion(string $region): self
  {
    $this->region = $region;
    return $this;
  }

  public function setRevenueValue(?float $value): self
  {
    $this->totalSubtotalCaFacture = $value ?? 0.0;
    return $this;
  }

  public function setCreditMemosValue(?float $value): self
  {
    $this->totalSubtotalCmFacture = $value ?? 0.0;
    return $this;
  }

  public function buildData(): array
  {
    $formattedData['region'] = $this->region;

    $formattedData['donutChartData'] = [
      ['label' => 'CHIFFRE D\'AFFAIRES', 'value' => $this->totalSubtotalCaFacture],
      ['label' => 'CREDIT MEMOS', 'value' => $this->totalSubtotalCmFacture],
    ];

    return $formattedData;
  }
}
