<?php

namespace App\Controller;

use DateTime;
use DateTimeZone;
use App\Entity\ListSalesInvoices;
use App\Entity\Stores;
use App\Repository\ListSalesInvoicesRepository;
use App\Repository\StoresRepository;
use App\Service\DataTableServices;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

use Symfony\Component\HttpClient\HttpClient;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Contracts\HttpClient\Exception\TransportExceptionInterface;

/**
 * Class SalesInvoiceController
 * @package App\Controller
 */

class SalesInvoiceController extends AbstractController
{
    private $storeRepository;

    public function __construct(StoresRepository $storeRepository)
    {
        $this->storeRepository = $storeRepository;
    }

    /**
     * @Route("/admin/api/salesinvoices/{param}", name="api_sales_invoices")
     */
    public function getSalesInvoicesApi(
        $param,
        Request $request,
        DataTableServices $dataTableServices,
        ListSalesInvoicesRepository $repository
    ): Response {
        $getRequest = $dataTableServices->getRequestDataTable($request);
        $salesInvoices = [];
        try {
            if ($param !== "all") {
                $param = $this->storeRepository->findOneByName($param)->getId();
                $salesInvoices = $repository->findSalesInvoiceByStore($param, $getRequest);
            } else {
                $salesInvoices = $repository->findSalesInvoice($getRequest);
            }

            return new JsonResponse($dataTableServices->dataTableConfig($request, $salesInvoices));
        } catch (\Exception $e) {

            return new JsonResponse($e->getMessage());
        } catch (TransportExceptionInterface $e) {

            return new JsonResponse($e->getMessage());
        }
    }

    /**
     * @Route("/admin/salesinvoices/{param}", name="salesinvoices")
     */
    public function getSalesInvoices($param)
    {
        return $this->render(
            'admin/salesinvoice.html.twig',
            [
                'tvainvoices' => [],
                'siteinvoice'  => $param
            ]
        );
    }

    /**
     * @Route("/admin/liste_sales_invoices/filter_by/{param}", name="liste_sales_invoices_by_store")
     */
    public function getListeSalesInvoiceFilterByStore(
        $param,
        Request $request,
        DataTableServices $dataTableServices,
        ListSalesInvoicesRepository $repository
    ) {
        $getRequest = $dataTableServices->getRequestDataTable($request);
        $stores = [];
        foreach (explode(',', $param) as $value) {
            $stores[] = $this->storeRepository->findOneByName($value)->getId();
        }

        try {
            $sales_invoices = $repository->findSalesInvoiceByStore($stores, $getRequest);

            return new JsonResponse($dataTableServices->dataTableConfig($request, $sales_invoices));
        } catch (\Exception $e) {
            return new JsonResponse($e->getMessage());
        } catch (TransportExceptionInterface $e) {
            return new JsonResponse($e->getMessage());
        }
    }

    /**
     * @Route("/admin/sales_invoice__api/{param}", name="all_sales_invoice_between_two_dates")
     */
    public function getAllCaFactureBetweenTwoDates(
        $param,
        Request $request,
        ListSalesInvoicesRepository $repository,
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
     * @Route("/admin/salesinvoices/{param}/json", name="salesinvoicesJson")
     */
    public function getSalesInvoicesJson($param)
    {

        try {
            $datetime = new DateTime('now', new DateTimeZone('Europe/Paris'));
            $datetime->modify("-40 days");
            $recentDate = $datetime->format('Y-m-d');
            if ($param != "all") {
                $storeId = $this->container->get('doctrine')->getRepository(Stores::class)->findOneByName($param)->getId();
                $salesInvoices = $this->getDoctrine()->getRepository(ListSalesInvoices::class)->findBySalesInvoices($recentDate, $storeId);
            } else {
                $salesInvoices = $this->getDoctrine()->getRepository(ListSalesInvoices::class)->findBySalesInvoices($recentDate);
            }
        } catch (\Exception $e) {

            return new JsonResponse($e->getMessage());
        } catch (TransportExceptionInterface $e) {

            return new JsonResponse($e->getMessage());
        }
        return new JsonResponse($salesInvoices);
    }

    public function getAllInvoices($param, $since_id, $lastid = false)
    {
        $allCount = "";
        if (!$lastid) {
            $datetime = new DateTime('now', new DateTimeZone('Europe/Paris'));
            $datetime2 = new DateTime('now', new DateTimeZone('Europe/Paris'));
            $maxSince = $since_id - 1;
            if (!$since_id) {
                $datetime->modify("-4 hours");
                $datetime2->modify("+4 hours");
            } else {
                $datetime->modify("-$since_id days");
                $datetime2->modify("-$maxSince days");
                $updated_max = $datetime2->format('c');
            }

            $updated_min = $datetime->format('c');


            $client = HttpClient::create();
            $url = "https://" . $this->getParameter('api_key_' . $param) . ":" . $this->getParameter('password_' . $param) . "@" . $this->getParameter('hostname_' . $param) . "/admin/api/" . $this->getParameter('version') . "/orders.json?status=any&limit=250&updated_at_min=" . $updated_min;
            if ($since_id) {
                $url = $url . "&updated_at_max=" . $updated_max;
            }
            //echo $url . "\n";
            $response = $client->request('GET', $url);
            $responses =  $response->toArray()['orders'];

            $lastid = 1;
            $count = 0;
            if (!empty($responses)) {
                $allCount = $count = count($responses);
                $lastid = $responses[$count - 1]['id'];
            }
        } else {
            $count = false;
            $responses = array();
            $i = 1;
            while (($count >= 250 || $count === false) && $i < 3) {
                $nextResponsesArray = $this->getNextOrders($param, $lastid);
                $nextResponses =  $nextResponsesArray[0];
                $count = $nextResponsesArray[1];
                $nextResponsesArray = null;
                $lastid = $nextResponses[$count - 1]['id'];
                $responses = array_merge($responses, $nextResponses);
                $allCount = $allCount + $count;
                $i++;
            }
            //echo $lastid . "\n";
        }

        //echo $lastid . "\n";
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
