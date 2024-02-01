<?php

namespace App\Controller;

use DateTime;
use DateTimeZone;
use App\Entity\Stores;
use App\Repository\ListTvaInvoicesRepository;
use App\Repository\StoresRepository;
use App\Service\DataTableServices;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

use Symfony\Component\HttpClient\HttpClient;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;

class TvaInvoiceController extends AbstractController
{

    private $storeRepository;

    public function __construct(StoresRepository $storeRepository)
    {
        $this->storeRepository = $storeRepository;
    }

    /**
     * @Route("/admin/api/tva_invoices/{param}",name="admin_api_tva_invoices")
     */
    public function getApiTvaInvoice(
        string $param,
        DataTableServices $dataTableServices,
        Request $request,
        ListTvaInvoicesRepository $repository
    ): Response {
        $tvaInvoices = [];
        $getRequest = $dataTableServices->getRequestDataTable($request);


        try {
            if ($param !== "all" && $param !== "allasia") {
                $param = $this->storeRepository->findOneByName($param)->getId();
                $tvaInvoices = $repository->findByStoreTvaInvoices($param, $getRequest);
            } else {
                $tvaInvoices = $repository->findTvaInvoices($getRequest);
            }
            return new JsonResponse($dataTableServices->dataTableConfig($request, $tvaInvoices));
        } catch (\Exception $th) {
            return new JsonResponse($th->getMessage());
        }
    }

    /**
     * @Route("/admin/api_tva_invoices/filter_by/{param}", name="api_tva_invoices_filter_store")
     */
    public function filterByStoreTvaInvoice(
        ListTvaInvoicesRepository $repository,
        DataTableServices $dataTableServices,
        $param,
        Request $request
    ): Response {

        $getRequest = $dataTableServices->getRequestDataTable($request);
        $stores = [];
        $liste_sales_invoices = [];

        foreach (explode(',', $param) as $value) {
            $stores[] = $this->storeRepository->findOneByName($value)->getId();
        }
        try {
            $liste_sales_invoices = $repository->findByStoreTvaInvoices($stores, $getRequest);
            return new JsonResponse($dataTableServices->dataTableConfig($request, $liste_sales_invoices));
        } catch (\Exception $e) {
            return new JsonResponse($e->getMessage());
        }
    }

    /**
     * @Route("/admin/tva_invoice__api__between_dates/{param}", name="all_tva_invoice_between_two_dates")
     */
    public function getAllCaFactureBetweenTwoDates(
        $param,
        Request $request,
        ListTvaInvoicesRepository $repository,
        DataTableServices $dataTableServices
    ) {
        $getRequest = $dataTableServices->getRequestDataTable($request);
        $data = [];
        if ($param != "all" && $param != "allasia") {
            $param = $this->storeRepository->findOneByName($param)->getId();
            $data = $repository->findBetweenTwoDates($getRequest, $param);
        } else {
            $data = $repository->findBetweenTwoDates($getRequest);
        }
        return new JsonResponse($dataTableServices->dataTableConfig($request, $data));
    }


    /**
     * @Route("/admin/tvainvoices/{param}", name="tvainvoices")
     */
    public function getTvaInvoices($param)
    {
        return $this->render(
            'admin/tvainvoice.html.twig',
            [
                'tvainvoices' => [],
                'siteinvoice'  => $param
            ]
        );
    }

    public function getAllTvaInvoices($param, $since_id, $lastId = false)
    {
        if (!$lastId) {
            $datetime = new DateTime('now', new DateTimeZone('Europe/Paris'));
            $datetime2 = new DateTime('now', new DateTimeZone('Europe/Paris'));
            $maxSince = $since_id - 1;
            if (!$since_id) {
                $datetime->modify("-4 hour");
                $datetime2->modify("+4 hour");
            } else {
                $datetime->modify("-$since_id days");
                $datetime2->modify("-$maxSince days");
            }

            $updated_min = $datetime->format('c');
            $updated_max = $datetime2->format('c');
            //echo $updated_min; exit;
            $client = HttpClient::create();
            $url = "https://" . $this->getParameter('api_key_' . $param) . ":" . $this->getParameter('password_' . $param) . "@" . $this->getParameter('hostname_' . $param) . "/admin/api/" . $this->getParameter('version') . "/orders.json?status=any&limit=250&updated_at_min=" . $updated_min;
            if ($since_id) {
                $url = $url . "&updated_at_max=" . $updated_max;
            }
            //echo $url; exit;
            $response = $client->request('GET', $url);
            $responses =  $response->toArray()['orders'];
            $allCount = 0;
            if (!empty($responses)) {
                $allCount = $count = count($responses);
                $lastId = $responses[$count - 1]['id'];
            }
        } else {
            $count = false;
            $allCount = 0;
            $responses = array();
            $i = 1;
            while (($count >= 250 || $count === false) && $i < 3) {
                $nextResponsesArray = $this->getNextOrders($param, $lastId);
                $nextResponses =  $nextResponsesArray[0];
                $count = $nextResponsesArray[1];
                $lastid = $nextResponses[$count - 1]['id'];
                $responses = array_merge($responses, $nextResponses);
                $allCount += $count;
                $i++;
            }
        }
        return array($responses, $allCount);
    }

    public function getNextOrders($param, $lastid)
    {
        $client = HttpClient::create();
        $url = "https://" . $this->getParameter('api_key_' . $param) . ":" . $this->getParameter('password_' . $param) . "@" . $this->getParameter('hostname_' . $param) . "/admin/api/" . $this->getParameter('version') . "/orders.json?status=any&limit=250&since_id=" . $lastid;
        $nextResponse = $client->request('GET', $url);
        $nextResponses =  $nextResponse->toArray()['orders'];
        $nextCount = count($nextResponses);
        return array($nextResponses, $nextCount);
    }

    public function getAllorders($param, $lastid)
    {
        $orders =  $this->getNextOrders($param, $lastid);
        return $orders;
    }
}
