<?php

namespace App\Controller;

use DateTime;
use DateTimeZone;
use App\Entity\Stores;
use App\Repository\ListSaleCreditMemoRepository;
use App\Repository\StoresRepository;
use App\Service\DataTableServices;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Annotation\Route;

use Symfony\Component\HttpClient\HttpClient;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Contracts\HttpClient\Exception\TransportExceptionInterface;

/**
 * Class SalesCreditMemoController
 * @package App\Controller
 */

class SalesCreditMemoController extends AbstractController
{
    private $storeRepository;

    public function __construct(StoresRepository $storeRepository)
    {
        $this->storeRepository = $storeRepository;
    }

    /**
     * @Route("/admin/api/salescreditmemos/{param}", name="api_sales_credit_memos")
     */
    public function getSalesCreitMemoApi(
        $param,
        Request $request,
        DataTableServices $dataTableServices,
        ListSaleCreditMemoRepository $repository
    ) {
        $getRequest = $dataTableServices->getRequestDataTable($request);

        $salesCeditmemo = [];
        try {

            if ($param !== "all" && $param !== "allasia") {
                $param = $this->storeRepository->findOneByName($param)->getId();
                $salesCeditmemo = $repository->findSalesCreditMemoByStore($param, $getRequest);
            } else {
                $salesCeditmemo = $repository->findSalesCreditMemo($getRequest);
            }

            return new JsonResponse($dataTableServices->dataTableConfig($request, $salesCeditmemo));
        } catch (\Exception $e) {

            return new JsonResponse($e->getMessage());
        } catch (TransportExceptionInterface $e) {

            return new JsonResponse($e->getMessage());
        }
    }

    /**
     * @Route("/admin/salescreditmemos/{param}", name="salescreditmemos")
     */
    public function getSalesCreditMemo($param)
    {
        return $this->render(
            'admin/salescreditmemo.html.twig',
            [
                'tvainvoices' => [],
                'siteinvoice'  => $param
            ]
        );
    }

    /**
     * @Route("/admin/sales_credit_memos/filter_by/{param}", name="sales_credit_memos_by_store")
     */
    public function getListeSalesCreditMemoFilterByStore(
        $param,
        Request $request,
        DataTableServices $dataTableServices,
        ListSaleCreditMemoRepository $repository
    ) {
        $getRequest = $dataTableServices->getRequestDataTable($request);
        $stores = [];
        foreach (explode(',', $param) as $value) {
            $stores[] = $this->storeRepository->findOneByName($value)->getId();
        }

        try {
            $salesCeditmemo = $repository->findSalesCreditMemoByStore($stores, $getRequest);
            return new JsonResponse($dataTableServices->dataTableConfig($request, $salesCeditmemo));
        } catch (\Exception $e) {

            return new JsonResponse($e->getMessage());
        } catch (TransportExceptionInterface $e) {

            return new JsonResponse($e->getMessage());
        }
    }

    /**
     * @Route("/admin/sales_credit_memo__api/{param}", name="all_sales_credit_memo_between_two_dates")
     */
    public function getAllSalesCreditMemoTwoDates(
        $param,
        Request $request,
        ListSaleCreditMemoRepository $repository,
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

    public function getAllCreditmemeo($param, $since_id)
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
        unset($response);
        $allCount = $count = count($responses);
        $lastid = 1;
        if (!empty($responses)) {
            $lastid = $responses[$count - 1]['id'];
        }
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
