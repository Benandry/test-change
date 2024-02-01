<?php

namespace App\Controller;

use DateTime;
use DateTimeZone;
use App\Entity\CaFacture;
use App\Entity\Stores;
use App\Repository\CaFactureRepository;
use App\Repository\StoresRepository;
use App\Service\DataTableServices;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Annotation\Route;

use Symfony\Component\HttpClient\HttpClient;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Contracts\HttpClient\Exception\TransportExceptionInterface;

/**
 * Class SalesInvoiceController
 * @package App\Controller
 */

class CaFactureController extends AbstractController
{

    private $storeRepository;

    public function __construct(StoresRepository $storeRepository)
    {
        $this->storeRepository = $storeRepository;
    }


    /**
     * @Route("/admin/api/ca_facture/{param}", name="api_ca_facture")
     */
    public function invoiceApiCaFacture(
        $param,
        Request $request,
        DataTableServices $dataTableServices,
        CaFactureRepository $repository
    ) {
        $getRequest = $dataTableServices->getRequestDataTable($request);
        $salesInvoices = [];
        try {
            if ($param !== "all" && $param !== "allasia") {
                $param = $this->storeRepository->findOneByName($param)->getId();
                $salesInvoices = $repository->findCaFactureByStore($param, $getRequest);
            } else {
                $salesInvoices = $repository->findCaFacture($getRequest);
            }
            $response = $dataTableServices->dataTableConfig($request, $salesInvoices);
        } catch (\Exception $e) {
            return new JsonResponse($e->getMessage());
        }
        return new JsonResponse($response);
    }

    /**
     * @Route("/admin/ajax/cafacture/europe/{param}", name="ajax_url_all_cafacture_europe")
     */
    public function getAllCaFactureToDisplayOnChart($param, CaFactureRepository $caFactureRepository)
    {
        $allCaFacture = [];

        if ($param === "all") {
            $allCaFacture = $caFactureRepository->findAllCaFactureEuro();
        }

        return new JsonResponse($allCaFacture);
    }

    /**
     * @Route("/admin/cafacture/{param}", name="cafacture")
     */
    public function getSalesInvoices($param, Request $request)
    {

        return $this->render(
            'admin/cafacture.html.twig',
            [
                'tvainvoices' => [],
                'siteinvoice'  => $param
            ]
        );
    }


    /**
     * @Route("/admin/cafacture/filter_by/{param}", name="cafacture_by_store")
     */
    public function getCaFactureFilterByStore(
        $param,
        Request $request,
        DataTableServices $dataTableServices,
        CaFactureRepository $repository
    ) {
        $getRequest = $dataTableServices->getRequestDataTable($request);
        $stores = [];
        foreach (explode(',', $param) as $value) {
            $stores[] = $this->storeRepository->findOneByName($value)->getId();
        }
        try {
            $salesInvoices = $repository->findCaFactureByStore($stores, $getRequest);
            return new JsonResponse($dataTableServices->dataTableConfig($request, $salesInvoices));
        } catch (\Exception $e) {

            return new JsonResponse([
                'success' => false,
                'error' => $e->getMessage(),
            ]);
        } catch (TransportExceptionInterface $e) {
            return new JsonResponse([
                'success' => false,
                'error' => $e->getMessage(),
            ]);
        }
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

    /**
     * @Route("/admin/cafacture__api/{param}", name="all_cafacture_between_two_dates")
     */
    public function getAllCaFactureBetweenTwoDates(
        $param,
        Request $request,
        CaFactureRepository $caFactureRepository,
        DataTableServices $dataTableServices
    ) {
        $getRequest = $dataTableServices->getRequestDataTable($request);
        if ($param != "all" && $param != "allasia") {
            $storeId = $this->storeRepository->findOneByName($param)->getId();
            $data = $caFactureRepository->findAllCaFactureBetweenTwoDates($getRequest, $storeId);
        } else {
            $data = $caFactureRepository->findAllCaFactureBetweenTwoDates($getRequest);
        }
        return new JsonResponse($dataTableServices->dataTableConfig($request, $data));
    }
}
