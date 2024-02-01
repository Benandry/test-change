<?php

namespace App\Controller;

use App\Service\DonutChartDataService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Annotation\Route;

class DonutChartDataController extends AbstractController
{
  private $donutChartDataService;

  public function __construct(DonutChartDataService $donutChartDataService)
  {
    $this->donutChartDataService = $donutChartDataService;
  }

  /**
   * @Route("/donut-chart-data-for-each-region", name="donut_chart_data_for_each_region")
   */
  public function index()
  {

    $donutChartDataForEachRegion = $this->donutChartDataService->getTotalOfRevenuesAndCreditMemosForEachRegion();

    return new JsonResponse($donutChartDataForEachRegion);
  }
}
