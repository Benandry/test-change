<?php

namespace App\Controller;

use App\Entity\User;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Doctrine\Persistence\ManagerRegistry;
use App\Entity\Stores;

class StoresController extends AbstractController
{
    /**
     * @Route("/admin/stores", name="stores")
     */
    public function index(): Response
    {
        $stores = $this->getDoctrine()->getRepository(stores::class)->findAll();
        return $this->render('stores/index.html.twig', [
            'stores' => $stores,
        ]);
    }

    /**
     * @Route("/admin/create-store", name="create-store")
     */
    public function createStore(ManagerRegistry $doctrine): Response
    {
        $entityManager = $doctrine->getManager();

        $store = new Stores();

        // tell Doctrine you want to (eventually) save the Product (no queries yet)
        $entityManager->persist($store);

        // actually executes the queries (i.e. the INSERT query)
        $entityManager->flush();

        return new Response('Saved new product with id '.$store->getId());
    }
}
