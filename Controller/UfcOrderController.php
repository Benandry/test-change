<?php

namespace App\Controller;

use App\Entity\UfcOrders;
use App\Repository\StoresRepository;
use App\Repository\UfcOrdersRepository;
use App\Service\DataTableServices;
use DateTime;
use DateTimeZone;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpClient\HttpClient;

class UfcOrderController extends AbstractController
{
    private StoresRepository $storeRepository;

    public function __construct(StoresRepository $storeRepository)
    {
        $this->storeRepository = $storeRepository;
    }

    /**
     * @Route("/admin/api/ufc_order/{param}", name="ufc_orders_api")
     */

    public function getApIUfcController(
        $param,
        DataTableServices $dataTableServices,
        Request $request,
        UfcOrdersRepository $repository
    ): Response {
        $getRequest = $dataTableServices->getRequestDataTable($request);
        $ufc_orders = [];

        try {
            if ($param !== "all" && $param !== "allasia") {
                $param = $this->storeRepository->findOneByName($param)->getId();
                $ufc_orders = $repository->findByStoreOrder($param, $getRequest);
            } else {
                $ufc_orders = $repository->findUfcOrder($getRequest);
            }
            return new JsonResponse($dataTableServices->dataTableConfig($request, $ufc_orders));
        } catch (\Exception $e) {
            return new JsonResponse($e->getMessage());
        }
    }

    /**
     * @Route("/admin/ufc_order/filter_by/{param}", name="ufc_order_by_store")
     */
    public function getUFCOrderByStore(
        $param,
        Request $request,
        DataTableServices $dataTableServices,
        UfcOrdersRepository $repository
    ) {
        $getRequest = $dataTableServices->getRequestDataTable($request);
        $ufc_orders = [];
        $stores = [];
        foreach (explode(',', $param) as $value) {
            $stores[] = $this->storeRepository->findOneByName($value)->getId();
        }
        $ufc_orders = $repository->findByStoreOrder($stores, $getRequest);
        return new JsonResponse($dataTableServices->dataTableConfig($request, $ufc_orders));
    }

    /**
     * @Route("/admin/ufc_order__api/{param}", name="ufc_order_between_two_dates")
     */
    public function getAllCaSkuBetweenTwoDates(
        $param,
        Request $request,
        UfcOrdersRepository $repo,
        DataTableServices  $dataTableServices
    ) {
        $getRequest = $dataTableServices->getRequestDataTable($request);
        $ufc_orders = [];
        if ($param != "all" && $param != "allasia") {
            $param = $this->storeRepository->findOneByName($param)->getId();
            $ufc_orders = $repo->findOrdertweenTwoDates($getRequest,  $param);
        } else {
            $ufc_orders = $repo->findOrdertweenTwoDates($getRequest);
        }
        return new JsonResponse($dataTableServices->dataTableConfig($request, $ufc_orders));
    }

    /**
     * @Route("/admin/ufc_order/{param}", name="ufc_orders_index")
     */
    public function index($param): Response
    {
        return $this->render('admin/ufc_order.html.twig', [
            'siteinvoice'  => $param
        ]);
    }


    public function getAllUfcOrder($param, $since_id): array
    {
        $datetime = new DateTime('now', new DateTimeZone('Europe/Paris'));
        $datetime2 = new DateTime('now', new DateTimeZone('Europe/Paris'));
        $maxSince = $since_id - 5;
        if (!$since_id) {
            $datetime->modify("-1 hour");
            $datetime2->modify("+1 hour");
        } else {
            $datetime->modify("-$since_id days");
            $datetime2->modify("-$maxSince days");
        }


        $client = HttpClient::create();
        $url = "https://" . $this->getParameter('api_key_' . $param) . ":" . $this->getParameter('password_' . $param) . "@" . $this->getParameter('hostname_' . $param) . "/admin/api/" . $this->getParameter('version') . "/orders.json?status=any&limit=250&since_id=" . $since_id;
        $response = $client->request('GET', $url);
        $responses =  $response->toArray()['orders'];
        $count = count($response->toArray()['orders']);
        return [$responses, $count];
    }
}
