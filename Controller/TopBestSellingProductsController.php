<?php

namespace App\Controller;

use App\Service\TopBestSellingProductsService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Annotation\Route;

class TopBestSellingProductsController extends AbstractController
{
  private $topBestSellingProductsService;

  public function __construct(TopBestSellingProductsService $topBestSellingProductsService)
  {
    $this->topBestSellingProductsService = $topBestSellingProductsService;
  }

  /**
   * @Route("/top-best-selling-products", name="top_best_selling_products")
   */
  public function index()
  {

    $topBestSellingProductsData = $this->topBestSellingProductsService->getTopBestSellingProductsByStore();

    return new JsonResponse($topBestSellingProductsData);
  }
}
