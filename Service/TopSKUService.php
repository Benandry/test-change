<?php

namespace App\Service;

class TopSKUService
{

  public function getTopSKUs(array $caSkus, int $storeId)
  {
    $filteredSkus = array_filter($caSkus, function ($entry) use ($storeId) {
      return $entry['store'] === $storeId;
    });

    // Construit un tableau associatif avec le SKU (sans la taille) comme clé et la quantité totale comme valeur
    $skuQuantities = [];

    foreach ($filteredSkus as $entry) {
      $sku = $entry['skuWithoutSize'];
      $totalQuantity = $entry['totalQuantity'];

      if (!isset($skuQuantities[$sku])) {
        $skuQuantities[$sku] = 0;
      }

      $skuQuantities[$sku] += $totalQuantity;
    }

    $result = [];

    foreach ($skuQuantities as $sku => $quantities) {
      $result[] = [$sku => $quantities];
    }

    return $result;
  }
}
