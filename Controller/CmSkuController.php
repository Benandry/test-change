<?php

namespace App\Controller;

use App\Service\DataTableServices;
use App\Repository\CmSkuRepository;
use App\Repository\StoresRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Annotation\Route;

use Symfony\Component\HttpClient\HttpClient;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;

/**
 * Class SalesInvoiceController
 * @package App\Controller
 */

class CmSkuController extends AbstractController
{
    private $storeRepository;

    public function __construct(StoresRepository $storeRepository)
    {
        $this->storeRepository = $storeRepository;
    }


    /**
     * @Route("/admin/api/cm_sku/{param}", name="api_cm_sku")
     */
    public function invoiceApiCaFacture(
        $param,
        Request $request,
        DataTableServices $dataTableServices,
        CmSkuRepository $repository
    ) {
        $getRequest = $dataTableServices->getRequestDataTable($request);
        $salesInvoices = [];
        try {
            if ($param !== "all" && $param !== "allasia") {
                $param =  $this->storeRepository->findOneByName($param)->getId();
                $salesInvoices = $repository->findByStoreCmSku($param, $getRequest);
            } else {
                $salesInvoices = $repository->findCmSku($getRequest);
            }
            return new JsonResponse($dataTableServices->dataTableConfig($request, $salesInvoices));
        } catch (\Exception $e) {
            return new JsonResponse($e->getMessage());
        }
    }

    /**
     * @Route("/admin/cmsku/{param}", name="cmsku")
     */
    public function getSalesInvoices($param)
    {
        return $this->render(
            'admin/cmsku.html.twig',
            [
                'tvainvoices' => [],
                'siteinvoice'  => $param
            ]
        );
    }
    /**
     * @Route("/admin/cmsku/filter_by/{param}", name="cmsku_by_store")
     */
    public function getCaComptableFilterByStore(
        $param,
        Request $request,
        DataTableServices $dataTableServices,
        CmSkuRepository $repository
    ) {
        $getRequest = $dataTableServices->getRequestDataTable($request);
        $stores = [];

        foreach (explode(',', $param) as $value) {
            $stores[] = $this->storeRepository->findOneByName($value)->getId();
        }
        $cm_sku = $repository->findByStoreCmSku($stores, $getRequest);
        return new JsonResponse($dataTableServices->dataTableConfig($request, $cm_sku));
    }
    /**
     * @Route("/admin/cmsku__api/{param}", name="all_cmsku_between_two_dates")
     */
    public function getAllCmSkuBetweenTwoDates(
        $param,
        DataTableServices $dataTableServices,
        Request $request,
        CmSkuRepository $cmSkuRepository
    ) {
        $getRequest = $dataTableServices->getRequestDataTable($request);


        if ($param != "all" && $param != "allasia") {
            $storeId =  $this->storeRepository->findOneByName($param)->getId();
            $data = $cmSkuRepository->findBetweenTwoDates($getRequest, $storeId);
        } else {
            $data = $cmSkuRepository->findBetweenTwoDates($getRequest);
        }
        return new JsonResponse($dataTableServices->dataTableConfig($request, $data));
    }


    public function getAllInvoices($param, $since_id)
    {
        $client = HttpClient::create();
        $url = "https://" . $this->getParameter('api_key_' . $param) . ":" . $this->getParameter('password_' . $param) . "@" . $this->getParameter('hostname_' . $param) . "/admin/api/" . $this->getParameter('version') . "/orders.json?financial_status=refunded,partially_refunded&limit=250&since_id=" . $since_id;
        $response = $client->request('GET', $url);
        $responses =  $response->toArray()['orders'];
        $count = count($response->toArray()['orders']);


        return array($responses, $count);
    }
}
