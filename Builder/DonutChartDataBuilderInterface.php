<?php

namespace App\Builder;

interface DonutChartDataBuilderInterface
{
  public function buildData($region): array;
}
