<?php

namespace App\Controller;

use DateTime;
use DateTimeZone;
use App\Repository\CmFactureRepository;
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

class CmFactureController extends AbstractController
{
    private $storeRepository;

    public function __construct(StoresRepository $storeRepository)
    {
        $this->storeRepository = $storeRepository;
    }
    /**
     * @Route("/admin/api/cm_facture/{param}", name="api_cm_facture")
     */
    public function invoiceApiCaFacture(
        $param,
        Request $request,
        DataTableServices $dataTableServices,
        CmFactureRepository $repository
    ) {
        $getRequest = $dataTableServices->getRequestDataTable($request);
        $salesInvoices = [];
        try {
            if ($param !== "all" && $param !== "allasia") {
                $param = $this->storeRepository->findOneByName($param)->getId();
                $salesInvoices = $repository->findByStoreCmFacture($param, $getRequest);
            } else {
                $salesInvoices = $repository->findByCmFacture($getRequest);
            }
            return new JsonResponse($dataTableServices->dataTableConfig($request, $salesInvoices));
        } catch (\Exception $e) {
            return new JsonResponse($e->getMessage());
        }
    }

    /**
     * @Route("/admin/cmfacture/{param}", name="cmfacture")
     */
    public function getSalesInvoices($param)
    {
        return $this->render(
            'admin/cmfacture.html.twig',
            [
                'tvainvoices' => [],
                'siteinvoice'  => $param
            ]
        );
    }


    /**
     * @Route("/admin/cmfacture/filter_by/{param}", name="cmfacture_by_store")
     */
    public function getCaComptableFilterByStore(
        $param,
        Request $request,
        DataTableServices $dataTableServices,
        CmFactureRepository $repository
    ) {
        $getRequest = $dataTableServices->getRequestDataTable($request);
        foreach (explode(',', $param) as $value) {
            $stores[] = $this->storeRepository->findOneByName($value)->getId();
        }
        $cm_facture = $repository->findByStoreCmFacture($stores, $getRequest);
        return new JsonResponse($dataTableServices->dataTableConfig($request, $cm_facture));
    }

    /**
     * @Route("/admin/cmfacture__api/twodate/{param}", name="all_cmfacture_between_two_dates")
     */
    public function getAllCmFactureBetweenTwoDates(
        $param,
        DataTableServices $dataTableServices,
        Request $request,
        CmFactureRepository $cmFactureRepository
    ) {
        $getRequest = $dataTableServices->getRequestDataTable($request);

        if ($param != "all" && $param != "allasia") {
            $storeId =  $this->storeRepository->findOneByName($param)->getId();
            $data = $cmFactureRepository->findBetweenDate($getRequest, $storeId);
        } else {
            $data = $cmFactureRepository->findBetweenDate($getRequest);
        }

        return new JsonResponse($dataTableServices->dataTableConfig($request, $data));
    }


    public function getAllInvoices($param, $since_id, $updated_at_max = null)
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
        // $url = "https://" . $this->getParameter('api_key_' . $param) . ":" . $this->getParameter('password_' . $param) . "@" . $this->getParameter('hostname_' . $param) . "/admin/api/" . $this->getParameter('version') . "/orders.json?financial_status=refunded,partially_refunded&limit=250&updated_at_min=" . $updated_at_min;
        // $url = "https://" . $this->getParameter('api_key_' . $param) . ":" . $this->getParameter('password_' . $param) . "@" . $this->getParameter('hostname_' . $param) . "/admin/api/" . $this->getParameter('version') . "/orders.json?financial_status=refunded,partially_refunded&limit=250&updated_at_max=" . $updated_at_max;
        $url = "https://" . $this->getParameter('api_key_' . $param) . ":" . $this->getParameter('password_' . $param) . "@" . $this->getParameter('hostname_' . $param) . "/admin/api/" . $this->getParameter('version') . "/orders.json?financial_status=refunded,partially_refunded&limit=250&since_id=" . $since_id;
        // if ($since_id) {
        //     $url = $url . "&updated_at_max=" . $updated_max;
        // }
        if ($updated_at_max) {
            $url = $url . "&updated_at_max=" . $updated_at_max;
        }
        //echo $url; exit;
        $response = $client->request('GET', $url);
        $responses =  $response->toArray()['orders'];
        unset($response);
        $allCount = $count = count($responses);
        $lastid = $responses[$count - 1]['id'];
        $i = 1;
        while ($count >= 250 && $i < 30) {
            $nextResponsesArray = $this->getNextOrders($param, $lastid);
            $nextResponses =  $nextResponsesArray[0];
            $count = $nextResponsesArray[1];
            unset($nextResponsesArray);
            $lastid = $nextResponses[$count - 1]['id'];
            $responses = array_merge($responses, $nextResponses);
            $allCount = $allCount + $count;
            $i++;
        }
        //echo $lastid;echo $lastid . "\n";
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
