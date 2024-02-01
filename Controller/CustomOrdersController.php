<?php

namespace App\Controller;

use App\Repository\ListCustomOrdersRepository;
use App\Repository\StoresRepository;
use App\Service\DataTableServices;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Annotation\Route;

use Symfony\Component\HttpClient\HttpClient;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;

/**
 * Class SalesInvoiceController
 * @package App\Controller
 */

class CustomOrdersController extends AbstractController
{
    private $storeRepository;

    public function __construct(StoresRepository $storeRepository)
    {
        $this->storeRepository = $storeRepository;
    }

    /**
     * @Route("/admin/api/custom_orders/{param}", name="api_list_custom_orders")
     */
    public function customOrderApi(
        $param,
        Request $request,
        DataTableServices $dataTableServices,
        ListCustomOrdersRepository $repository
    ) {
        $getRequest = $dataTableServices->getRequestDataTable($request);
        $salesInvoices = [];
        try {
            if ($param !== "all" && $param !== "allasia") {
                $param = $this->storeRepository->findOneByName($param)->getId();
                $salesInvoices = $repository->findCustomOrderByStore($param, $getRequest);
            } else {
                $salesInvoices = $repository->findCustomOrder($getRequest);
            }
            return new JsonResponse($dataTableServices->dataTableConfig($request, $salesInvoices));
        } catch (\Exception $e) {
            return new JsonResponse($e->getMessage());
        }
    }

    /**
     * @Route("/admin/customorders/{param}", name="customorders")
     */
    public function getCustomOrders($param)
    {

        return $this->render(
            'admin/customorders.html.twig',
            [
                'customorders' => [],
                'siteinvoice'  => $param
            ]
        );
    }

    /**
     * @Route("/admin/list_custom_order/filter_by/{param}", name="api_filter_list_custom_order")
     */
    public function getCaFactureFilterByStore(
        $param,
        Request $request,
        DataTableServices $dataTableServices,
        ListCustomOrdersRepository $repository
    ) {
        $getRequest = $dataTableServices->getRequestDataTable($request);
        $list_orders = [];

        $stores = [];
        foreach (explode(',', $param) as $value) {
            $stores[] = $this->storeRepository->findOneByName($value)->getId();
        }
        $list_orders = $repository->findCustomOrderByStore($stores, $getRequest);
        return new JsonResponse($dataTableServices->dataTableConfig($request, $list_orders));
    }
    /**
     * @Route("/admin/list_custom_order_between_two_date_api/{param}", name="all_list_custom_order_between_two")
     */
    public function getAllistCustomOrderBetweenTwoDates(
        $param,
        Request $request,
        ListCustomOrdersRepository $repository,
        DataTableServices $dataTableServices
    ) {
        $getRequest = $dataTableServices->getRequestDataTable($request);

        if ($param != "all" && $param != "allasia") {
            $param = $this->storeRepository->findOneByName($param)->getId();
            $data = $repository->findBetweenTwoDates($getRequest, $param);
        } else {
            $data = $repository->findBetweenTwoDates($getRequest);
        }
        return new JsonResponse($dataTableServices->dataTableConfig($request, $data));
    }

    public function getAllCustomOrders($param, $since_id)
    {
        $client = HttpClient::create();
        $url = "https://" . $this->getParameter('api_key_' . $param) . ":" . $this->getParameter('password_' . $param) . "@" . $this->getParameter('hostname_' . $param) . "/admin/api/" . $this->getParameter('version') . "/orders.json?financial_status=paid&limit=250&since_id=" . $since_id;
        $response = $client->request('GET', $url);
        $responses =  $response->toArray()['orders'];
        $count = count($response->toArray()['orders']);

        return array($responses, $count);
    }
}
