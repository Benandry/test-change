<?php

namespace App\Controller;

use App\Repository\CaNewSkuRepository;
use App\Repository\StoresRepository;
use App\Service\DataTableServices;
use DateTime;
use DateTimeZone;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpClient\HttpClient;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class CaNewSkuController extends AbstractController
{
    private $storeRepository;

    public function __construct(StoresRepository $storeRepository)
    {
        $this->storeRepository = $storeRepository;
    }

    /**
     * @Route("/admin/api/ca_new_sku/{param}", name="app_api_ca_new_sku")
     */
    public function invoiceApiCaFacture(
        $param,
        Request $request,
        DataTableServices $dataTableServices,
        CaNewSkuRepository $repository
    ): Response {
        $getRequest = $dataTableServices->getRequestDataTable($request);

        $ca_new_sku = [];

        try {
            if ($param !== "all" && $param !== "allasia") {
                $param =  $this->storeRepository->findOneByName($param)->getId();
                $ca_new_sku = $repository->findByStoreCaNewSku($param, $getRequest);
            } else {
                $ca_new_sku = $repository->findCaNewSku($getRequest);
            }

            return new JsonResponse($dataTableServices->dataTableConfig($request, $ca_new_sku));
        } catch (\Exception $e) {
            return new JsonResponse($e->getMessage());
        }
    }


    /**
     * @Route("/admin/ca_new_sku/filter_by/{param}", name="app_ca_new_sku_filter_by_store")
     */
    public function getCaComptableFilterByStore(
        $param,
        Request $request,
        DataTableServices $dataTableServices,
        CaNewSkuRepository $repository
    ) {
        $getRequest = $dataTableServices->getRequestDataTable($request);
        $ca_new_sku = [];
        $stores = [];
        foreach (explode(',', $param) as $value) {
            $stores[] = $this->storeRepository->findOneByName($value)->getId();
        }
        try {
            $ca_new_sku = $repository->findByStoreCaNewSku($stores, $getRequest);
            return new JsonResponse($dataTableServices->dataTableConfig($request, $ca_new_sku));
        } catch (\Exception $e) {
            return new JsonResponse($e->getMessage());
        }
    }

    /**
     * @Route("/admin/ca_new_sku-twod-dates/{param}", name="all_ca_new_sku_between_two_dates")
     */
    public function getAllCaSkuBetweenTwoDates(
        $param,
        Request $request,
        CaNewSkuRepository $repository,
        DataTableServices  $dataTableServices
    ) {
        $getRequest = $dataTableServices->getRequestDataTable($request);
        $data = [];

        if ($param != "all" && $param != "allasia") {
            $stores = $this->getStoreParams($param);
            $data = $repository->findDataBetweenDates($getRequest, $stores);
        } else {
            $data = $repository->findDataBetweenDates($getRequest);
        }
        return new JsonResponse($dataTableServices->dataTableConfig($request, $data));
    }




    /**
     * @Route("/admin/ca_new_sku/{param}", name="app_ca_new_sku_")
     */
    public function index($param): Response
    {
        return $this->render(
            'admin/canewsku.html.twig',
            [
                'tvainvoices' => [],
                'siteinvoice'  => $param
            ]
        );
    }
    private function getStoreParams($param): array
    {
        $stores = [];
        foreach (explode(',', $param) as $value) {
            $stores[] = $this->storeRepository->findOneByName($value)->getId();
        }
        return  $stores;
    }



    public function getCaNewSku($storeData, $orderId)
    {
        if (!$storeData) {
            return;
        }

        $orders = $this->getShopifyOrder($storeData['store']);
        $result = [];
        foreach ($orders as $order) {
            $baseline = $this->getInvoiceBaseline($order, $storeData['storeId']);
            $productsline = $this->getInvoiceProductsLine($order);
            $discountLine = $this->getInvoiceDiscountsLine($order);
            $shippingLine = $this->getInvoiceShippingsLine($order);

            $result[] = [
                'products' => array_merge($baseline, $productsline),
                'shipping' => array_merge($baseline, $shippingLine),
                'discount' => array_merge($baseline, $discountLine),
            ];
        }
        return $result;
    }

    protected function getShopifyOrder($param)
    {

        try {
            $client = HttpClient::create();

            $url = "https://" . $this->getParameter('api_key_' . $param) . ":" . $this->getParameter('password_' . $param) . "@" . $this->getParameter('hostname_' . $param) . "/admin/api/" . $this->getParameter('version') . "//orders.json?status=any&limit=250";

            $response = $client->request('GET', $url);
            $order =  $response->toArray();
            return $order['orders'];
        } catch (\Exception $e) {
            echo $e->getMessage();
        }
    }

    protected function getInvoiceBaseline($order, $param)
    {
        $product = [];
        $product['productId'] = '';
        $product['numCommande'] = $order["order_number"];
        $product['store'] = $param;
        $product['dateFacture'] =  $order["created_at"];
        $product['numFacture'] =  $order["order_number"];
        $product['typeDeBien'] = '';
        $product['sku'] = '';
        $product['sinceId'] = '';
        $product['name'] = "";
        $product['totalQtyOrder'] = 1;
        if (($param == 23) || ($param == 24) || ($param == 25) || ($param == 27)) {
            $product['isVisible'] = 1;
        } else {
            $product['isVisible'] = 0;
        }
        $product['priceHt'] = 0.00;
        $product['montantHt'] =  0.00;
        $product['montantTTC'] =  0.00;
        $product['company'] = $order['shipping_address']['company'] ?? '';
        $product['companyFacturaation'] = $order['billing_address']['company'] ?? '';
        $product['nomClient']  = $order['customer']["first_name"] . " " . $order['customer']["last_name"];
        $product['paysLivraison'] = $order['shipping_address']['country_code'];
        return $product;
    }


    protected function getInvoiceProductsLine($order)
    {
        $product = [];
        $linesItems = $order['line_items'];
        $tax = 0;
        if (!empty($order['tax_lines'])) {
            $tax = $order['tax_lines'][0]['rate'];
        }

        $product['typeDeBien'] = 'Produit';
        $discountUnit = 0;
        foreach ($linesItems as $linesItem) {
            $product['productId'] = $linesItem['product_id'];
            $product['sku'] = $linesItem['sku'];
            $product['name'] =  $linesItem['name'];
            $product['totalQtyOrder'] =  $linesItem['quantity'];
            $appliedDiscounts = $linesItem['discount_allocations'];
            if (isset($appliedDiscounts) && !empty($appliedDiscounts)) {
                foreach ($appliedDiscounts as $appliedDiscount) {
                    $discountHt = +$appliedDiscount['amount'] / (1 + $tax);
                }
                $discountUnit = $discountHt / $linesItem['quantity'];
            }
            $itemPriceHt = ($linesItem['price'] / (1 + $tax)) - $discountUnit;
            $itemMontantHt = $itemPriceHt * $linesItem['quantity'];
            $itemMontantTTC =  $itemMontantHt  + $tax;
            $product['priceHt'] = round($itemPriceHt, 2);
            $product['montantHt'] = round($itemMontantHt, 2);
            $product['montantTTC'] = round($itemMontantTTC, 2);
        }
        return $product;
    }

    protected function getInvoiceDiscountsLine($order)
    {
        $product = array();
        $tax = 0;
        if (!empty($order['tax_lines'])) {
            $tax = $order['tax_lines'][0]['rate'];
        }
        $linesItems = $order['line_items'];
        $totalDiscount = 0;
        foreach ($linesItems as $linesItem) {
            $discountHt = 0;
            $appliedDiscounts = $linesItem['discount_allocations'];
            if (isset($appliedDiscounts) && !empty($appliedDiscounts)) {
                foreach ($appliedDiscounts as $appliedDiscount) {
                    $discountHt += $appliedDiscount['amount'] / (1 + $tax);
                    $totalDiscount += $discountHt;
                }
            }
            $itemMontantTTC = $totalDiscount + $tax;
            $product['priceHt'] = $discountHt;
            $product['montantHt'] = round($totalDiscount, 2);
            $product['montantTTC'] = round($itemMontantTTC, 2);
            $product['company'] = $order["shipping_address"]['company'];
        }


        $product['typeDeBien'] = 'Discount';
        return $product;
    }

    protected function getInvoiceShippingsLine($order)
    {
        $product = array();
        $shippings = $order['shipping_lines'];
        $tax = 0;
        if (!empty($order['tax_lines'])) {
            $tax = $order['tax_lines'][0]['rate'];
        }
        $totalShippingPrice = 0;
        foreach ($shippings as $shipping) {
            $shippingPrice = 0;
            if ($shipping['price'] <= 0) {
                continue;
            }
            $shippingPrice =  $shipping['price'] / (1 + $tax);
            $totalShippingPrice += $shippingPrice;
        }
        $product['montantHt'] = round($totalShippingPrice, 2);
        $product['typeDeBien'] = 'Livraison';
        return $product;
    }
}
