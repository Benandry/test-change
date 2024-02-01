<?php

namespace App\Controller;

use DateTime;
use DateTimeZone;
use App\Entity\Tvaoss;
use App\Entity\Stores;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

use Symfony\Component\HttpClient\HttpClient;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Contracts\HttpClient\Exception\TransportExceptionInterface;

class TvaossController extends AbstractController
{

    /**
     * @Route("/admin/tvaoss/{param}", name="tvaoss")
     */
    public function getTvaoss($param)
    {
        $datetime = new DateTime('now', new DateTimeZone('Europe/Paris'));
        $datetime->modify("-40 days");
        $recentDate = $datetime->format('Y-m-d');
        //var_dump($recentDate); exit;
        try {
            if ($param!="all") {
                $storeId = $this->container->get('doctrine')->getRepository(Stores::class)->findOneByName($param)->getId();
                $tvaoss = $this->getDoctrine()->getRepository(Tvaoss::class)->findByLastTvaoss($recentDate, $storeId);
            }else {
                $stores = array(4,14,15,16,20);
                $tvaoss = $this->getDoctrine()->getRepository(Tvaoss::class)->findByLastTvaoss($recentDate);
            }
            $finalOss = array();
            $countryAllowed = array('AT','BG','HR','CY','CZ','DK','EE','FI','DE','GR','HU','IE','IT','LV','LT','LU','MT','NL','PL','PT','RO','SK','SI','ES','SE');
            foreach($tvaoss as $tvaos) {
               if(preg_match('/\#/i',$tvaos->getNumFacture())) {
                   continue;
               }
               if(!in_array($tvaos->getPaysArrivee(), $countryAllowed)) {
                continue;
            }
               $finalOss[]= $tvaos;
            }
        } catch (\Exception $e) {
            return new JsonResponse($e->getMessage());
        } catch (TransportExceptionInterface $e) {

            return new JsonResponse($e->getMessage());
        }

        return $this->render(
            'admin/tvaoss.html.twig',
            [
                'tvaoss' => $finalOss,
                'siteinvoice'  => $param
            ]);

    }

    /**
     * @Route("/admin/tvaoss/{param}/json", name="tvaossJson")
     */
    public function getTvaossJson($param)
    {
        $datetime = new DateTime('now', new DateTimeZone('Europe/Paris'));
        $datetime->modify("-70 days");
        $recentDate = $datetime->format('Y-m-d');
        // var_dump($recentDate); exit;
        try {
            if ($param!="all") {
                $storeId = $this->container->get('doctrine')->getRepository(Stores::class)->findOneByName($param)->getId();
                $tvaoss = $this->getDoctrine()->getRepository(Tvaoss::class)->findByTvaoss($recentDate, $storeId);
            }else {
                //$stores = array(4,14,15,16,20);
                $tvaoss = $this->getDoctrine()->getRepository(Tvaoss::class)->findByTvaoss($recentDate);
            }
            $finalOss = array();
            $countryAllowed = array('AT','BG','HR','CY','CZ','DK','EE','FI','DE','GR','HU','IE','IT','LV','LT','LU','MT','NL','PL','PT','RO','SK','SI','ES','SE');
            foreach($tvaoss as $tvaos) {
                if(preg_match('/\#/i',$tvaos['num_facture'])) {
                   continue;
               }
               if(!in_array($tvaos['pays_arrivee'], $countryAllowed)) {
                continue;
            }
               $finalOss['data'][]= array_values($tvaos);
            }
        } catch (\Exception $e) {
            return new JsonResponse($e->getMessage());
        } catch (TransportExceptionInterface $e) {

            return new JsonResponse($e->getMessage());
        }
        return new JsonResponse($finalOss);

    }

    public function getShippings($param, $orderId)
    {
        
        if(!$param || !$orderId){
            return;
        }
        $order = $this->getShopifyOrder($param, $orderId);
        $shippings = $order['shipping_lines'];
        if($shippings) {
            $products = array();
            foreach($shippings as $shipping) {
                if($shipping['price']<=0) {
                    continue;
                }
                $product = array();
                $tax = 0;
                if(isset($order['tax_lines'][0])){
                    $tax = $order['tax_lines'][0]['rate'];
                }
                
                $itemPriceHt = $shipping['price'] / (1 + $tax);
                $itemRowTotalHt = $itemPriceHt;
                $itemRowTotalTtc = $shipping['price'];
                $montantTva = $itemRowTotalTtc - $itemRowTotalHt;
                $bAddress = $order['billing_address'];
                $sAddress = $order['shipping_address'];
                $shippingDate = date('Y-m-d H:i:s', strtotime($order['created_at'] . " +2 days"));
                $product['date_facture'] = date('Y-m-d H:i:s', strtotime($order['created_at']));
                $product['num_facture'] = $order['name'];
                $product['order_id'] = $order['id'];
                $product['type_operation'] = 'Débit';
                $product['type_bien'] = 'Livraison';
                $product['type_service'] = '';
                $product['qty'] = 1;
                $product['prix_unitaire'] = (float) round($itemPriceHt,2);
                $product['montant_total_ht'] = (float) round($itemRowTotalHt,2);
                $product['taux_tva'] = 100*$tax;
                $product['montant_tva'] = (float) round($montantTva,2);
                $product['montant_ttc'] = (float) round($itemRowTotalTtc,2);
                $product['devise'] = $order['currency'];
                $product['date_livraison'] = $shippingDate;
                $product['pays_depart'] = 'France';
                $product['pays_arrivee'] = $sAddress['country_code'];
                $product['client_addresse'] = $bAddress['address1'] . ' ' . $bAddress['city'] . ' ' . $bAddress['address1'] . ' ' . $bAddress['zip'] . ' ' . $bAddress['country_code'] ;
                $product['nom_client'] = $order['billing_address']['first_name'] . ' ' . $order['billing_address']['last_name'];
                $product['date_paiement'] = date('Y-m-d H:i:s', strtotime($order['created_at']));
                $product['montant_paiement'] = (float) round($itemRowTotalTtc,2);
                $product['accompte'] = 0;
                $product['lien_facture'] = '';
                $product['store'] = $param;
                $product['type_id'] = 'livraison_produits';
                $products[]=$product;
            }
        }
        return $products;
    }

    public function getRefundedShipping($param, $orderId, $creditId)
    {
        if(!$param || !$orderId){
            return;
        }
        $order = $this->getShopifyOrder($param, $orderId);
        $refunds = $order['refunds'];
        if($refunds) {
            $products = array();
            foreach($refunds as $refund){
                if($refund['id']==$creditId){
                    if(isset($refund['order_adjustments']) && !empty($refund['order_adjustments'])) {
                        $refundLines = $refund['order_adjustments'];
                        foreach($refundLines as $refundLine) {
                            if($refundLine['kind']!='shipping_refund') {
                                continue;
                            }
                            $product = array();
                            $tax = 0;
                            if(isset($order['tax_lines'][0])){
                                $tax = $order['tax_lines'][0]['rate'];
                            }
                            $itemPriceHt = abs($refundLine['amount']);
                            $itemRowTotalHt = abs($refundLine['amount']);
                            $itemRowTotalTtc = abs($refundLine['amount'] * (1 + $tax));
                            $montantTva = $itemRowTotalTtc - $itemRowTotalHt;
                            $bAddress = $order['billing_address'];
                            $sAddress = $order['shipping_address'];
                            $shippingDate = date('Y-m-d H:i:s', strtotime($order['created_at'] . " +2 days"));
                            $product['date_facture'] = date('Y-m-d H:i:s', strtotime($refund['created_at']));
                            $product['num_facture'] = $order['name'];
                            $product['order_id'] = $creditId;
                            $product['type_operation'] = 'Débit';
                            $product['type_bien'] = 'Livraison';
                            $product['type_service'] = '';
                            $product['qty'] = 1;
                            $product['prix_unitaire'] = (float) round('-' . $itemPriceHt,2);
                            $product['montant_total_ht'] = (float) round('-' . $itemRowTotalHt,2);
                            $product['taux_tva'] = 100*$tax;
                            $product['montant_tva'] = (float) round('-' . $montantTva,2);
                            $product['montant_ttc'] = (float) round('-' . $itemRowTotalTtc,2);
                            $product['devise'] = $order['currency'];
                            $product['date_livraison'] = $shippingDate;
                            $product['pays_depart'] = 'France';
                            $product['pays_arrivee'] = $sAddress['country_code'];
                            $product['client_addresse'] = $bAddress['address1'] . ' ' . $bAddress['city'] . ' ' . $bAddress['address1'] . ' ' . $bAddress['zip'] . ' ' . $bAddress['country_code'] ;
                            $product['nom_client'] = $order['billing_address']['first_name'] . ' ' . $order['billing_address']['last_name'];
                            $product['date_paiement'] = date('Y-m-d H:i:s', strtotime($order['created_at']));
                            $product['montant_paiement'] = (float) round('-' . $itemRowTotalTtc,2);
                            $product['accompte'] = 0;
                            $product['lien_facture'] = '';
                            $product['store'] = $param;
                            $product['type_id'] = 'avoir_livraisons';
                            $products[]=$product;
                        }
                    }
                }
            }
        }
        return $products;
    }

    public function getRefundedProducts($param, $orderId, $creditId)
    {
        if(!$param || !$orderId){
            return;
        }
        $order = $this->getShopifyOrder($param, $orderId);
        $refunds = $order['refunds'];
        if($refunds) {
            $products = array();
            foreach($refunds as $refund){
                if($refund['id']==$creditId) {
                    $refundLines = $refund['refund_line_items'];
                    foreach($refundLines as $refundLine) {
                        $product = array();
                        $tax = 0;
                        if(isset($order['tax_lines'][0])){
                            $tax = $order['tax_lines'][0]['rate'];
                        }
                        $itemPriceHt = $refundLine['line_item']['pre_tax_price'];
                        $itemRowTotalHt = $refundLine['subtotal'] / (1 + $tax);
                        $itemRowTotalTtc = $refundLine['subtotal'];
                        $montantTva = $itemRowTotalTtc - $itemRowTotalHt;
                        $bAddress = $order['billing_address'];
                        $sAddress = $order['shipping_address'];
                        $shippingDate = date('Y-m-d H:i:s', strtotime($order['created_at'] . " +2 days"));
                        $product['date_facture'] = date('Y-m-d H:i:s', strtotime($refund['created_at']));
                        $product['num_facture'] = $order['name'];
                        $product['order_id'] = $creditId;
                        $product['type_operation'] = 'Débit';
                        $product['type_bien'] = $refundLine['line_item']['name'];;
                        $product['type_service'] = '';
                        $product['qty'] = $refundLine['line_item']['quantity'];
                        $product['prix_unitaire'] = (float) round('-' . $itemPriceHt,2);
                        $product['montant_total_ht'] = (float) round('-' . $itemRowTotalHt,2);
                        $product['taux_tva'] = 100*$tax;
                        $product['montant_tva'] = (float) round('-' . $montantTva,2);
                        $product['montant_ttc'] = (float) round('-' . $itemRowTotalTtc,2);
                        $product['devise'] = $order['currency'];
                        $product['date_livraison'] = $shippingDate;
                        $product['pays_depart'] = 'France';
                        $product['pays_arrivee'] = $sAddress['country_code'];
                        $product['client_addresse'] = $bAddress['address1'] . ' ' . $bAddress['city'] . ' ' . $bAddress['address1'] . ' ' . $bAddress['zip'] . ' ' . $bAddress['country_code'] ;
                        $product['nom_client'] = $order['billing_address']['first_name'] . ' ' . $order['billing_address']['last_name'];
                        $product['date_paiement'] = date('Y-m-d H:i:s', strtotime($order['created_at']));
                        $product['montant_paiement'] = (float) round('-' . $itemRowTotalTtc,2);
                        $product['accompte'] = 0;
                        $product['lien_facture'] = '';
                        $product['store'] = $param;
                        $product['type_id'] = 'avoir_produits';
                        $products[]=$product;
                    }
                }
            }
        }
        return $products;
    }
    
    public function getOrderProducts($param, $orderId)
    {
        if(!$param || !$orderId){
            return;
        }

        $order = $this->getShopifyOrder($param, $orderId);
        $linesItems = $order['line_items'];
        $products = array();
        if($linesItems) {
            foreach($linesItems as $linesItem){
                $product = array();
                $tax = 0;
                if(isset($order['tax_lines'][0])){
                    $tax = $order['tax_lines'][0]['rate'];
                }
                $discountHt = 0;
                $discountTtc = 0;
                $discountUnit = 0;
                $appliedDiscounts = $linesItem['discount_allocations'];
                
                if(isset($appliedDiscounts) && !empty($appliedDiscounts)) {
                    foreach($appliedDiscounts as $appliedDiscount) {
                        $discountHt =+ $appliedDiscount['amount']/(1+$tax);
                        $discountTtc =+ $appliedDiscount['amount'];
                    }
                    $discountUnit = $discountHt/$linesItem['quantity'];
                }
                $itemPriceHt = ($linesItem['price']/(1+$tax)) - $discountUnit;
                $itemRowTotalHt = $itemPriceHt * $linesItem['quantity'];
                $itemRowTotalTtc = $linesItem['price'] * $linesItem['quantity'] - $discountTtc;
                $montantTva = $itemRowTotalTtc - $itemRowTotalHt;
                $bAddress = $order['billing_address'];
                $sAddress = $order['shipping_address'];
                $shippingDate = date('Y-m-d H:i:s', strtotime($order['created_at'] . " +2 days"));
                
                $product['date_facture'] = date('Y-m-d H:i:s', strtotime($order['created_at']));
                $product['num_facture'] = $order['name'];
                $product['order_id'] = $order['id'];
                $product['type_operation'] = 'Débit';
                $product['type_bien'] = $linesItem['name'];
                $product['type_service'] = '';
                $product['qty'] = $linesItem['quantity'];
                $product['prix_unitaire'] = round($itemPriceHt,2);
                $product['montant_total_ht'] = round($itemRowTotalHt,2);
                $product['taux_tva'] = 100*$tax;
                $product['montant_tva'] = round($montantTva,2);
                $product['montant_ttc'] = round($itemRowTotalTtc,2);
                $product['devise'] = $order['currency'];
                $product['date_livraison'] = $shippingDate;
                $product['pays_depart'] = 'France';
                $product['pays_arrivee'] = $sAddress['country_code'];
                $product['client_addresse'] = $bAddress['address1'] . ' ' . $bAddress['city'] . ' ' . $bAddress['address1'] . ' ' . $bAddress['zip'] . ' ' . $bAddress['country_code'] ;
                $product['nom_client'] = $order['billing_address']['first_name'] . ' ' . $order['billing_address']['last_name'];
                $product['date_paiement'] = date('Y-m-d H:i:s', strtotime($order['created_at']));
                $product['montant_paiement'] = round($itemRowTotalTtc,2);
                $product['accompte'] = 0;
                $product['lien_facture'] = '';
                $product['store'] = $param;
                $product['type_id'] = 'facture_produits';
                $products[]=$product;
            }
        }
        return $products;
    }

    private function getShopifyOrder($param, $orderId)
    {
        try {
            $client = HttpClient::create();
            $url = "https://" . $this->getParameter('api_key_' . $param) . ":" . $this->getParameter('password_' . $param) . "@" . $this->getParameter('hostname_' . $param) . "/admin/api/" . $this->getParameter('version') . "/orders/" . $orderId . ".json";
            $response = $client->request('GET', $url);
            $order =  $response->toArray()['order'];
            return $order;
        }catch(\Exception $e){
            //echo $e->getMessage();
            //echo $orderId . ' - ' . $param; exit;
        }
    }
}