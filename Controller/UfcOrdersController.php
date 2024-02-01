<?php

namespace App\Controller;

use App\Repository\ListUfcOrdersRepository;
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

class UfcOrdersController extends AbstractController
{
    private StoresRepository $storeRepository;

    public function __construct(StoresRepository $storeRepository)
    {
        $this->storeRepository = $storeRepository;
    }

    /**
     * @Route("/admin/api/ufcorders__/{param}", name="api_ufcorders__index")
     */
    public function invoiceApiCaFacture(
        $param,
        Request $request,
        DataTableServices $dataTableServices,
        ListUfcOrdersRepository $repository
    ) {
        $getRequest = $dataTableServices->getRequestDataTable($request);
        $ufcOrders = [];
        try {
            if ($param !== "all" && $param !== "allasia") {
                $param = $this->storeRepository->findOneByName($param)->getId();
                $ufcOrders = $repository->findUfcOrderByStore($param, $getRequest);
            } else {
                $ufcOrders = $repository->findUfcOrderALl($getRequest);
            }
            return new JsonResponse($dataTableServices->dataTableConfig($request, $ufcOrders));
        } catch (\Exception $e) {
            return new JsonResponse($e->getMessage());
        }
    }

    /**
     * @Route("/admin/ufcorders__/filter_by/{param}", name="app_ufcorders_filter_by")
     */
    public function getCaFactureFilterByStore(
        $param,
        Request $request,
        DataTableServices $dataTableServices,
        ListUfcOrdersRepository $repository
    ) {
        $getRequest = $dataTableServices->getRequestDataTable($request);
        $ufcOrders = [];
        $stores = [];
        foreach (explode(',', $param) as $value) {
            $stores[] = $this->storeRepository->findOneByName($value)->getId();
        }
        try {
            $ufcOrders = $repository->findUfcOrderByStore($stores, $getRequest);
            return new JsonResponse($dataTableServices->dataTableConfig($request, $ufcOrders));
        } catch (\Exception $e) {

            return new JsonResponse([
                'success' => false,
                'error' => $e->getMessage(),
            ]);
        }
    }
    /**
     * @Route("/admin/ufcorders_between_/{param}", name="app_all_ufcorders_between_dates")
     */
    public function getAllCaFactureBetweenTwoDates(
        $param,
        Request $request,
        ListUfcOrdersRepository $repo,
        DataTableServices $dataTableServices
    ) {
        $getRequest = $dataTableServices->getRequestDataTable($request);
        if ($param != "all" && $param != "allasia") {
            $storeId = $this->storeRepository->findOneByName($param)->getId();
            $ufcOrders = $repo->findBetweenTwoDates($getRequest, $storeId);
        } else {
            $ufcOrders = $repo->findBetweenTwoDates($getRequest);
        }

        return new JsonResponse($dataTableServices->dataTableConfig($request, $ufcOrders));
    }


    /**
     * @Route("/admin/ufcorders/{param}", name="ufcorders")
     */
    public function getUfcOrders($param)
    {

        return $this->render(
            'admin/ufcorders.html.twig',
            [
                'ufcorders' => [],
                'siteinvoice'  => $param
            ]
        );
    }

    public function getAllUfcOrders($param, $since_id)
    {
        $client = HttpClient::create();
        $url = "https://" . $this->getParameter('api_key_' . $param) . ":" . $this->getParameter('password_' . $param) . "@" . $this->getParameter('hostname_' . $param) . "/admin/api/" . $this->getParameter('version') . "/orders.json?financial_status=paid&limit=250&since_id=" . $since_id;

        $response = $client->request('GET', $url);
        $responses =  $response->toArray()['orders'];
        $allCount = $count = count($responses);
        $lastid = $responses[$count - 1]['id'];
        $i = 1;
        while ($count >= 250 && $i < 100) {
            $nextResponsesArray = $this->getNextOrders($param, $lastid);
            $nextResponses =  $nextResponsesArray[0];
            $count = $nextResponsesArray[1];
            $lastid = $nextResponses[$count - 1]['id'];
            $responses = array_merge($responses, $nextResponses);
            $allCount = $allCount + $count;
            $i++;
        }

        //echo $lastid;
        return array($responses, $allCount);
    }

    public function getUfcProduct($param, $product_id, $sku)
    {
        $client = HttpClient::create();
        $url = "https://" . $this->getParameter('api_key_' . $param) . ":" . $this->getParameter('password_' . $param) . "@" . $this->getParameter('hostname_' . $param) . "/admin/api/" . $this->getParameter('version') . "/products/" . $product_id . ".json";
        $response = $client->request('GET', $url);
        $responses =  $response->toArray()['product']['variants'];
        foreach ($responses as $respons) {
            if ($respons['sku'] == $sku) {
                $taille = $respons['title'];
                $tags = explode(',', $response->toArray()['product']['tags']);
                $j = count($tags);
                $flocage = "";
                for ($i = 0; $i < $j; $i++) {
                    if (strpos($tags[$i], 'color_') !== false) {
                        $color = str_replace('color_', '', $tags[$i]);
                    }
                    if (strpos($tags[$i], 'flocage_') !== false) {
                        $flocage = str_replace('flocage_', '', $tags[$i]);
                    }
                    if (strpos($tags[$i], 'gender_') !== false) {
                        $gendre = substr(str_replace('gender_', '', $tags[$i]), 0, 2);
                    }
                }
                $type = $response->toArray()['product']['product_type'] . ' / ' . $gendre;
            }
        }
        return array($taille, $color, $type, $flocage);
    }

    public function getNextOrders($param, $lastid)
    {
        $client = HttpClient::create();
        $url = "https://" . $this->getParameter('api_key_' . $param) . ":" . $this->getParameter('password_' . $param) . "@" . $this->getParameter('hostname_' . $param) . "/admin/api/" . $this->getParameter('version') . "/orders.json?financial_status=paid&limit=250&since_id=" . $lastid;
        $nextResponse = $client->request('GET', $url);
        $nextResponses =  $nextResponse->toArray()['orders'];
        $nextCount = count($nextResponses);
        return array($nextResponses, $nextCount);
    }
}
