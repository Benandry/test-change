<?php

namespace App\Service;

interface MorrisBarChartDataBuilderInterface
{
  // public function buildData(array $stores, array $caSkus): array;
  public function buildData(int $storeId, string $storeName): array;
}
