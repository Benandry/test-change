<?php

namespace App\Controller;

use DateTime;
use DateTimeZone;
use App\Entity\ListTvaCreditmemo;
use App\Entity\Stores;
use App\Repository\ListTvaCreditmemoRepository;
use App\Repository\StoresRepository;
use App\Service\DataTableServices;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

use Symfony\Component\HttpClient\HttpClient;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;

class TvaCreditmemoController extends AbstractController
{

    private $storeRepository;

    public function __construct(StoresRepository $storeRepository)
    {
        $this->storeRepository = $storeRepository;
    }
    /**
     * @Route("/admin/api/tva_credit_memo/{param}",name="admin_api_tva_credit_memo")
     */
    public function getApiTvaInvoice(
        string $param,
        DataTableServices $dataTableServices,
        Request $request,
        ListTvaCreditmemoRepository $repository
    ): Response {
        $tvaInvoices = [];

        $getRequest = $dataTableServices->getRequestDataTable($request);

        try {
            if ($param !== "all" && $param !== "allasia") {
                $param = $this->storeRepository->findOneByName($param)->getId();
                $tvaInvoices = $repository->findByStoreTvaCreditMemo($param, $getRequest);
            } else {
                $tvaInvoices = $repository->findTvaCreditMemo($getRequest);
            }
            return new JsonResponse($dataTableServices->dataTableConfig($request, $tvaInvoices));
        } catch (\Exception $th) {
            return new JsonResponse($th->getMessage());
        }
    }

    /**
     * @Route("/admin/api_tva_credit_memo/filter_by/{param}", name="api_tva_credit_memo_filter_store")
     */
    public function filterByStoreTvaInvoice(
        ListTvaCreditmemoRepository $repository,
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
            $liste_sales_invoices = $repository->findByStoreTvaCreditMemo($stores, $getRequest);
            return new JsonResponse($dataTableServices->dataTableConfig($request, $liste_sales_invoices));
        } catch (\Exception $e) {
            return new JsonResponse($e->getMessage());
        }
    }

    /**
     * @Route("/admin/tva_creditmemo_api__between_dates/{param}", name="all_tva_creditmemo_between_two_dates")
     */
    public function getAllCaFactureBetweenTwoDates(
        Request $request,
        $param,
        ListTvaCreditmemoRepository $repository,
        DataTableServices $dataTableServices
    ) {
        $getRequest = $dataTableServices->getRequestDataTable($request);

        if ($param != "all" && $param != "allasia") {
            $param = $this->storeRepository->findOneByName($param)->getId();
            $data = $repository->findBetweenTwoDates($getRequest, $param);
        } else {
            $data = $repository->findBetweenTwoDates($getRequest);
        }
        $response = $dataTableServices->dataTableConfig($request, $data);

        return new JsonResponse($response);
    }

    /**
     * @Route("/admin/tvacreditmemo/{param}", name="tvacreditmemo")
     */
    public function getTvaCreditmemo($param)
    {
        return $this->render(
            'admin/tvacreditmemo.html.twig',
            [
                'tvainvoices' => [],
                'siteinvoice'  => $param
            ]
        );
    }

    public function getAllTvaCreditmemo($param, $since_id)
    {
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
        $client = HttpClient::create();
        $url = "https://" . $this->getParameter('api_key_' . $param) . ":" . $this->getParameter('password_' . $param) . "@" . $this->getParameter('hostname_' . $param) . "/admin/api/" . $this->getParameter('version') . "/orders.json?financial_status=refunded,partially_refunded&limit=250&updated_at_min=" . $updated_min;
        if ($since_id) {
            $url = $url . "&updated_at_max=" . $updated_max;
        }
        //echo $url; exit;
        $response = $client->request('GET', $url);
        $responses =  $response->toArray()['orders'];
        $allCount = $count = count($responses);
        $lastid = 1;
        if (!empty($responses)) {
            $lastid = $responses[$count - 1]['id'];
        }
        $i = 1;
        while ($count >= 250 && $i < 10) {
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

    public function getNextOrders($param, $lastid, $updated_at_min = false)
    {
        if (!$lastid) {
            return;
        }
        $client = HttpClient::create();
        $url = "https://" . $this->getParameter('api_key_' . $param) . ":" . $this->getParameter('password_' . $param) . "@" . $this->getParameter('hostname_' . $param) . "/admin/api/" . $this->getParameter('version') . "/orders.json?financial_status=refunded,partially_refunded&limit=250&since_id=" . $lastid;
        if ($updated_at_min) {
            $url .= "&updated_at_min=" . $updated_at_min;
        }
        $nextResponse = $client->request('GET', $url);
        $nextResponses =  $nextResponse->toArray()['orders'];
        $nextCount = count($nextResponses);
        return array($nextResponses, $nextCount);
    }
}
