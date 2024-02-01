<?php

namespace App\Controller;

use App\Service\DataTableServices;
use DateTime;
use DateTimeZone;
use App\Repository\CaSkuRepository;
use App\Repository\StoresRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

use Symfony\Component\HttpClient\HttpClient;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Contracts\HttpClient\Exception\TransportExceptionInterface;

/**
 * Class SalesInvoiceController
 * @package App\Controller
 */

class CaSkuController extends AbstractController
{
    private $storeRepository;

    public function __construct(StoresRepository $storeRepository)
    {
        $this->storeRepository = $storeRepository;
    }

    /**
     * @Route("/admin/api/ca_sku/{param}", name="api_ca_sku")
     */
    public function invoiceApiCaFacture($param, Request $request, DataTableServices $dataTableServices, CaSkuRepository $repository)
    {
        $getRequest = $dataTableServices->getRequestDataTable($request);
        $except =  $getRequest['except'];
        $searchValue =  $getRequest['searchValue'];
        $start =  $getRequest['start'];
        $length =  $getRequest['length'];
        $typeStore = $getRequest['typeStore'];

        $salesInvoices = [];
        try {
            if ($param !== "all" && $param !== "allasia") {
                $param =  $this->storeRepository->findOneByName($param)->getId();
                if (!empty($searchValue)) {
                    $salesInvoices = $repository->findByWordField($searchValue, $start, $length, $typeStore, $param);
                } else {
                    $salesInvoices = $repository->findByStoreCaSku($param, $start, $length);
                }
            } else {
                if (!empty($searchValue)) {
                    $salesInvoices = $repository->findByWordField($searchValue, $start, $length, $typeStore);
                } else {
                    if ($param == "allasia") {
                        // All store Asia
                        $salesInvoices = $repository->findCaSkuAsia($start, $length);
                    } else {
                        if ($except) {
                            $salesInvoices = $repository->findCaSkuExceptUk($start, $length);
                        } else {
                            $salesInvoices = $repository->findStoreVisibleById($start, $length);
                        }
                    }
                }
            }

            $response = $dataTableServices->dataTableConfig($request, $salesInvoices);
        } catch (\Exception $e) {

            return new JsonResponse($e->getMessage());
        } catch (TransportExceptionInterface $e) {

            return new JsonResponse($e->getMessage());
        }
        return new JsonResponse($response);
    }



    /**
     * @Route("/admin/casku/{param}", name="casku")
     */
    public function getSalesInvoices($param)
    {
        // dd( $salesInvoices);
        return $this->render(
            'admin/casku.html.twig',
            [
                'tvainvoices' => [],
                'siteinvoice'  => $param
            ]
        );
    }

    /**
     * @Route("/admin/casku/filter_by/{param}", name="casku_by_store")
     */
    public function getCaComptableFilterByStore($param, Request $request, DataTableServices $dataTableServices, CaSkuRepository $repository)
    {
        $getRequest = $dataTableServices->getRequestDataTable($request);
        $searchValue =  $getRequest['searchValue'];
        $start =  $getRequest['start'];
        $length =  $getRequest['length'];
        $minDate = $getRequest['minDate'];
        $maxDate = $getRequest['maxDate'];
        $typeStore = $getRequest['typeStore'];
        $ca_sku = [];
        $stores = [];

        foreach (explode(',', $param) as $value) {
            $stores[] = $this->storeRepository->findOneByName($value)->getId();
        }
        try {
            if (!empty($searchValue)) {
                $ca_sku = $repository->findByWordField($searchValue, $start, $length, $typeStore, $stores);
            } else {
                if ($minDate && $maxDate) {
                    $ca_sku = $repository->findByStoreCaSku($stores, $start, $length, $minDate, $maxDate);
                } else {
                    $ca_sku = $repository->findByStoreCaSku($stores, $start, $length);
                }
            }
            $response = $dataTableServices->dataTableConfig($request, $ca_sku);
        } catch (\Exception $e) {

            return new JsonResponse($e->getMessage());
        } catch (TransportExceptionInterface $e) {

            return new JsonResponse($e->getMessage());
        }
        return new JsonResponse($response);
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
        $updated_at_max = '2024-01-10T23:59:59';

        $client = HttpClient::create();
        // $url = "https://" . $this->getParameter('api_key_' . $param) . ":" . $this->getParameter('password_' . $param) . "@" . $this->getParameter('hostname_' . $param) . "/admin/api/" . $this->getParameter('version') . "/orders.json?status=any&limit=250&since_id=" . $since_id;
        $url = "https://" . $this->getParameter('api_key_' . $param) . ":" . $this->getParameter('password_' . $param) . "@" . $this->getParameter('hostname_' . $param) . "/admin/api/" . $this->getParameter('version') . "/orders.json?status=any&limit=250&updated_at_max=" . $updated_at_max;
        $response = $client->request('GET', $url);
        $responses =  $response->toArray()['orders'];
        $count = count($response->toArray()['orders']);


        return array($responses, $count);
    }

    /**
     * @Route("/admin/casku__api/{param}", name="all_casku_between_two_dates")
     */
    public function getAllCaSkuBetweenTwoDates($param, Request $request, CaSkuRepository $caSkuRepository, DataTableServices  $dataTableServices)
    {
        $getRequest = $dataTableServices->getRequestDataTable($request);
        $except =  $getRequest['except'];
        $searchValue =  $getRequest['searchValue'];
        $start =  $getRequest['start'];
        $length =  $getRequest['length'];
        $minDate = $getRequest['minDate'];
        $maxDate = $getRequest['maxDate'];
        $asiaSite = $getRequest['asiaSite'];
        $typeStore = $getRequest['typeStore'];

        $data = [];
        if ($param != "all" && $param != "allasia") {
            $param =  $this->storeRepository->findOneByName($param)->getId();
            if (!empty($searchValue)) {
                $data = $caSkuRepository->findByWordField($searchValue, $start, $length, $typeStore, $param);
            } else {
                $data = $caSkuRepository->findAllCaParSKUBetweenTwoDates($minDate, $maxDate, $except, $asiaSite, $start, $length, $param);
            }
        } else {
            $data = $caSkuRepository->findAllCaParSKUBetweenTwoDates($minDate, $maxDate, $except, $asiaSite, $start, $length);
            if (!empty($searchValue)) {
                $data = $caSkuRepository->findByWordField($searchValue, $start, $length, $typeStore);
            }
        }
        return new JsonResponse($dataTableServices->dataTableConfig($request, $data));
    }
}
