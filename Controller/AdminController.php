<?php

namespace App\Controller;

use App\Entity\ListSalesInvoices;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class AdminController extends AbstractController
{
    /**
     * @Route("/admin", name="admin")
     */
    public function index(): Response
    {
        $yesterday = new \DateTime('yesterday', new \DateTimezone('UTC'));


        return $this->render('admin/index.html.twig', [
            'yesterdayDate' => $yesterday->format('d/m/Y'),
        ]);
    }
}
