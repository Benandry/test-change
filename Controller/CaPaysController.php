<?php

namespace App\Controller;

use App\Service\DataTableServices;
use DateTime;
use DateTimeZone;
use App\Repository\CaPaysRepository;
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

class CaPaysController extends AbstractController
{
    private $storeRepository;

    public function __construct(StoresRepository $storeRepository)
    {
        $this->storeRepository = $storeRepository;
    }

    /**
     * @Route("/admin/api/ca_pays/{param}", name="api_ca_pays")
     */
    public function invoiceApiCaFacture(
        $param,
        Request $request,
        DataTableServices $dataTableServices,
        CaPaysRepository $repository
    ) {
        $getRequest = $dataTableServices->getRequestDataTable($request);

        try {
            if ($param !== "all" && $param !== "allasia") {
                $param =  $this->storeRepository->findOneByName($param)->getId();
                $salesInvoices = $repository->findByStoreCaPays($param, $getRequest);
            } else {
                $salesInvoices = $repository->findCaPays($getRequest);
            }

            return new JsonResponse($dataTableServices->dataTableConfig($request, $salesInvoices));
        } catch (\Exception $e) {
            return new JsonResponse($e->getMessage());
        }
    }


    /**
     * @Route("/admin/capays/{param}", name="capays")
     */
    public function getSalesInvoices($param, Request $request)
    {
        return $this->render(
            'admin/capays.html.twig',
            [
                'tvainvoices' => [],
                'siteinvoice'  => $param
            ]
        );
    }

    /**
     * @Route("/admin/caPays/filter_by/{param}", name="capays_by_store")
     */
    public function getCaComptableFilterByStore(
        $param,
        Request $request,
        DataTableServices $dataTableServices,
        CaPaysRepository $repository
    ) {
        $getRequest = $dataTableServices->getRequestDataTable($request);
        $ca_pays = [];
        $stores = [];

        foreach (explode(',', $param) as $value) {
            $stores[] = $this->storeRepository->findOneByName($value)->getId();
        }
        $ca_pays = $repository->findByStoreCaPays($stores, $getRequest);
        return new JsonResponse($dataTableServices->dataTableConfig($request, $ca_pays));
    }

    /**
     * @Route("/admin/capays__api/{param}", name="all_capays_between_two_dates")
     */
    public function getAllCaPaysBetweenTwoDates(
        $param,
        DataTableServices $dataTableServices,
        Request $request,
        CaPaysRepository $caPaysRepository
    ) {
        $getRequest = $dataTableServices->getRequestDataTable($request);
        $data = [];

        if ($param != "all" && $param != "allasia") {
            $param =  $this->storeRepository->findOneByName($param)->getId();
            $data = $caPaysRepository->findBetweenDate($getRequest, $param);
        } else {
            $data = $caPaysRepository->findBetweenDate($getRequest);
        }
        return new JsonResponse($dataTableServices->dataTableConfig($request, $data));
    }


    public function getAllInvoices($param, $since_id)
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

        $updated_min = $datetime->format('c');
        $updated_max = $datetime2->format('c');

        $client = HttpClient::create();
        $url = "https://" . $this->getParameter('api_key_' . $param) . ":" . $this->getParameter('password_' . $param) . "@" . $this->getParameter('hostname_' . $param) . "/admin/api/" . $this->getParameter('version') . "/orders.json?status=any&limit=250&since_id=" . $since_id;
        $response = $client->request('GET', $url);
        $responses =  $response->toArray()['orders'];
        $count = count($response->toArray()['orders']);


        return array($responses, $count);
    }
}
