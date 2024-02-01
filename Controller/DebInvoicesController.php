<?php

namespace App\Controller;

use App\Entity\DebInvoices;
use App\Entity\Stores;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

use Symfony\Component\HttpClient\HttpClient;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Contracts\HttpClient\Exception\TransportExceptionInterface;

class DebInvoicesController extends AbstractController
{
    /**
     * @Route("/admin/debinvoices/{param}", name="debinvoices")
     */
    public function getDebinvoices($param)
    {
        try {
            if ($param != "all") {
                $storeId = $this->container->get('doctrine')->getRepository(Stores::class)->findOneByName($param)->getId();
                $debinvoices = $this->getDoctrine()->getRepository(DebInvoices::class)->findBy(['store_id' => $storeId, 'type_id' => 'invoice']);
            } else {
                $debinvoices = $this->getDoctrine()->getRepository(DebInvoices::class)->findBy(['type_id' => 'invoice']);
            }
        } catch (\Exception $e) {
            return new JsonResponse($e->getMessage());
        } catch (TransportExceptionInterface $e) {
            return new JsonResponse($e->getMessage());
        }

        return $this->render('admin/deb_invoices.html.twig', [
            'debinvoices' => $debinvoices,
            'siteinvoice' => $param,
        ]);
    }

    /**
     * @Route("/admin/debcreditsmemos/{param}", name="debcreditsmemos")
     */
    public function getDebcreditsmemos($param)
    {

        try {
            if ($param != "all") {
                $storeId = $this->container->get('doctrine')->getRepository(Stores::class)->findOneByName($param)->getId();
                $debcreditsmemos = $this->getDoctrine()->getRepository(DebInvoices::class)->findBy(['store_id' => $storeId, 'type_id' => 'credit']);
            } else {
                $debcreditsmemos = $this->getDoctrine()->getRepository(DebInvoices::class)->findBy(['type_id' => 'credit']);
            }
        } catch (\Exception $e) {
            return new JsonResponse($e->getMessage());
        } catch (TransportExceptionInterface $e) {
            return new JsonResponse($e->getMessage());
        }

        return $this->render('admin/deb_creditsmemos.html.twig', [
            'debcreditsmemos' => $debcreditsmemos,
            'siteinvoice' => $param,
        ]);
    }

    public function getInvoicesData($storeData, $orderId)
    {
        if (!$storeData || !$orderId) {
            return;
        }

        $order = $this->getShopifyOrder($storeData['store'], $orderId);
        $products = array();
        $baseline = $this->getInvoiceBaseline($order, $storeData['storeId']);
        $productsline = $this->getInvoiceProductsLine($order);
        $discountLine = $this->getInvoiceDiscountsLine($order);
        $shippingLine = $this->getInvoiceShippingsLine($order);


        return array(
            'products' => array_merge($baseline, $productsline),
            'shipping' => array_merge($baseline, $shippingLine),
            'discount' => array_merge($baseline, $discountLine),
        );
    }

    public function getCreditsData($storeData, $creditId, $orderId)
    {
        if (!$storeData || !$orderId) {
            return;
        }

        $order = $this->getShopifyOrder($storeData['store'], $orderId);
        $products = array();
        $baseline = $this->getCreditBaseline($order, $storeData['storeId'], $creditId);
        $productsline = $this->getCreditProductsLine($order, $creditId);
        $discountLine = $this->getCreditDiscountsLine($order, $creditId);
        $shippingLine = $this->getCreditShippingsLine($order, $creditId);


        return array(
            'products' => array_merge($baseline, $productsline),
            'shipping' => array_merge($baseline, $shippingLine),
            'discount' => array_merge($baseline, $discountLine),
        );
    }

    protected function getInvoiceBaseline($order, $param)
    {
        $product = array();
        $sAddress = $order['shipping_address'];
        $product['pays_destination'] = $sAddress['country_code'];
        $product['nature_transport'] = 11;
        $product['mode_transport'] = 3;
        $product['departement_arrivee'] = 94;
        $product['tva_intracom'] = '';
        $product['num_facture'] = '';
        $product['nom_client'] = isset($order['company']) && !empty($order['company']) ? $order['company'] : '';
        $product['date_facture'] = '';
        $product['order_id'] = $order['id'];
        $product['store_id'] = $param;
        $product['type_id'] = 'invoice';

        return $product;
    }

    protected function getCreditBaseline($order, $param, $creditId)
    {
        $product = array();
        $sAddress = $order['shipping_address'];
        $product['pays_destination'] = $sAddress['country_code'];
        $product['nature_transport'] = 11;
        $product['mode_transport'] = 3;
        $product['departement_arrivee'] = 94;
        $product['tva_intracom'] = '';
        $product['num_facture'] = '';
        $product['nom_client'] = isset($order['company']) && !empty($order['company']) ? $order['company'] : '';
        $product['date_facture'] = '';
        $product['order_id'] = $creditId;
        $product['store_id'] = $param;
        $product['type_id'] = 'credit';

        return $product;
    }

    protected function getInvoiceProductsLine($order)
    {
        $product = array();
        $linesItems = $order['line_items'];
        $tax = $order['tax_lines'][0]['rate'];
        $grams = 0;
        $rowTotalHt = 0;
        $qtys = 0;
        foreach ($linesItems as $linesItem) {
            $discountHt = 0;
            $appliedDiscounts = $linesItem['discount_allocations'];
            if (isset($appliedDiscounts) && !empty($appliedDiscounts)) {
                foreach ($appliedDiscounts as $appliedDiscount) {
                    $discountHt = +$appliedDiscount['amount'] / (1 + $tax);
                }
                $discountUnit = $discountHt / $linesItem['quantity'];
            }
            $itemPriceHt = ($linesItem['price'] / (1 + $tax)) - $discountUnit;
            $itemRowTotalHt = $itemPriceHt * $linesItem['quantity'];
            $rowTotalHt += $itemRowTotalHt;
            $grams += $linesItem['grams'];
            $qtys += $linesItem['quantity'];
        }
        $product['regime_statique'] = 21;
        $product['valeur_ht'] = round($rowTotalHt, 2);
        $product['masse_kg'] = round($grams / 1000, 2);
        $product['unite_supplementaire'] = $qtys;
        $product['detail_nommenclature'] = '';
        return $product;
    }

    protected function getCreditProductsLine($order, $creditId)
    {
        $product = array();
        $refunds = $order['refunds'];
        if ($refunds) {
            $tax = $order['tax_lines'][0]['rate'];
            $grams = 0;
            $rowTotalHt = 0;
            $qtys = 0;
            foreach ($refunds as $refund) {
                if ($refund['id'] == $creditId) {
                    $refundLines = $refund['refund_line_items'];
                    foreach ($refundLines as $refundLine) {
                        $itemRowTotalHt = $refundLine['subtotal'] / (1 + $tax);
                        $rowTotalHt += $itemRowTotalHt;
                        $grams += $refundLine['line_item']['grams'];
                        $qtys += $refundLine['line_item']['quantity'];
                    }
                }
            }
        }
        $product['regime_statique'] = 25;
        $product['valeur_ht'] = (float) round('-' . $rowTotalHt, 2);
        $product['masse_kg'] = round($grams / 1000, 2);
        $product['unite_supplementaire'] = $qtys;
        $product['detail_nommenclature'] = '';
        return $product;
    }

    protected function getInvoiceShippingsLine($order)
    {
        $product = array();
        $shippings = $order['shipping_lines'];
        $tax = $order['tax_lines'][0]['rate'];
        $totalShippingPrice = 0;
        foreach ($shippings as $shipping) {
            $shippingPrice = 0;
            if ($shipping['price'] <= 0) {
                continue;
            }
            $shippingPrice =  $shipping['price'] / (1 + $tax);
            $totalShippingPrice += $shippingPrice;
        }
        $product['regime_statique'] = 26;
        $product['valeur_ht'] = round($totalShippingPrice, 2);
        $product['masse_kg'] = 0;
        $product['unite_supplementaire'] = 0;
        $product['detail_nommenclature'] = 'Shipping';
        return $product;
    }

    protected function getCreditShippingsLine($order, $creditId)
    {
        $product = array();
        $refunds = $order['refunds'];
        $tax = $order['tax_lines'][0]['rate'];
        $totalShippingPrice = 0;
        if ($refunds) {
            foreach ($refunds as $refund) {
                if ($refund['id'] == $creditId) {
                    if (isset($refund['order_adjustments']) && !empty($refund['order_adjustments'])) {
                        $refundLines = $refund['order_adjustments'];
                        foreach ($refundLines as $refundLine) {
                            if ($refundLine['kind'] != 'shipping_refund') {
                                continue;
                            }
                            $itemRowTotalHt = abs($refundLine['amount']);
                            $totalShippingPrice += $itemRowTotalHt;
                        }
                    }
                }
            }
        }
        $product['regime_statique'] = 25;
        $product['valeur_ht'] = (float) round('-' . $totalShippingPrice, 2);
        $product['masse_kg'] = 0;
        $product['unite_supplementaire'] = 0;
        $product['detail_nommenclature'] = 'Shipping';
        return $product;
    }

    protected function getInvoiceDiscountsLine($order)
    {
        $product = array();
        $tax = $order['tax_lines'][0]['rate'];
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
        }
        $product['regime_statique'] = 25;
        $product['valeur_ht'] = round($totalDiscount, 2);
        $product['masse_kg'] = 0;
        $product['unite_supplementaire'] = 0;
        $product['detail_nommenclature'] = 'Discount';
        return $product;
    }

    protected function getCreditDiscountsLine($order, $creditId)
    {
        $product = array();
        $product['regime_statique'] = 25;
        $product['valeur_ht'] = 0;
        $product['masse_kg'] = 0;
        $product['unite_supplementaire'] = 0;
        $product['detail_nommenclature'] = 'Discount';
        return $product;
    }

    protected function getShopifyOrder($param, $orderId)
    {
        try {
            $client = HttpClient::create();
            $url = "https://" . $this->getParameter('api_key_' . $param) . ":" . $this->getParameter('password_' . $param) . "@" . $this->getParameter('hostname_' . $param) . "/admin/api/" . $this->getParameter('version') . "/orders/" . $orderId . ".json";
            $response = $client->request('GET', $url);
            $order =  $response->toArray()['order'];
            return $order;
        } catch (\Exception $e) {
            echo $e->getMessage();
            //echo $orderId . ' - ' . $param; exit;
        }
    }
}
