<?php

namespace App\Controller;

use App\Entity\UfcProducts;
use phpDocumentor\Reflection\Element;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Validator\Validator\ValidatorInterface;

class UfcProductsController extends AbstractController
{
    /**
     * @Route("/admin/ufc/products", name="ufc_products")
     */
    public function index(): Response
    {
        return $this->render('ufc_products/index.html.twig', [
            'controller_name' => 'UfcProductsController',
        ]);
    }

}
