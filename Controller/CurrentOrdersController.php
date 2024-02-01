<?php

namespace App\Controller;

use App\Entity\User;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use App\Entity\Token;
use App\Utils\TokenHandle;

use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\HttpClient\HttpClient;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\ResponseHeaderBag;
use Symfony\Component\HttpFoundation\Session\Session;
use Symfony\Contracts\HttpClient\Exception\TransportExceptionInterface;

/**
 * Class SalesInvoiceController
 * @package App\Controller
 */

class CurrentOrdersController extends AbstractController
{

    /**
     * @Route("/admin/currentorders/{param}", name="currentorders")
     */
    public function getCurrentOrders($param)
    {
        try {
            $client = HttpClient::create();
            
            if ($param!="all") {
                $since_id=0;
                $url = "https://" . $this->getParameter('api_key_' . $param) . ":" . $this->getParameter('password_' . $param) . "@" . $this->getParameter('hostname_' . $param) . "/admin/api/" . $this->getParameter('version') . "/orders.json?financial_status=paid&fulfillment_status=unfulfilled&limit=250&since_id=".$since_id;
                $response = $client->request('GET', $url);
                $responses =  $response->toArray()['orders'];
                $count=count($response->toArray()['orders']);
                $since_id = $responses[$count-1]['id'];
                if(($since_id!=0)&& ($count>=250)) {
                    for($i=1; $i<=4;$i++) {
                        $result = $this->getAllInvoices($param, $since_id);
                        $allInvoices = $result['0'];
                        $since_id = $result['1'];
                        $count = $result['2'];
                        $responses = array_merge($allInvoices, $responses);
                    }
                }

            }else {

                $sites=explode(',',$this->getParameter('api_site'));
                $responses = array();
                foreach ($sites as $site) {
                    $siteOrders = $this->getSiteCurrentOrders($site);
                    $responseFinal = array();
                    foreach($siteOrders as $order) {
                        $order['store'] = $site;
                        $responseFinal[] = $order;
                    }
                    $responses = array_merge($responses, $responseFinal);
                }
            }

            // Filter shipped orders
            $currentsoOrders = array();
            foreach($responses as $response) {
                if(preg_match('/SHIPPED/', $response['tags'])){
                    continue;
                }
                $currentsoOrders[] = $response;
            }

        } catch (\Exception $e) {

            return new JsonResponse($e->getMessage());

        } catch (TransportExceptionInterface $e) {

            return new JsonResponse($e->getMessage());
        }

        //var_dump($response->toArray()['orders']); die();
        return $this->render(
            'admin/currentordres.html.twig',
            [
                'tvainvoices' => $currentsoOrders,
                'siteinvoice'  => $param,
                'url' => $url
            ]);

    }
    public function getAllInvoices($param, $since_id)
    {
        $client = HttpClient::create();
        $url = "https://" . $this->getParameter('api_key_' . $param) . ":" . $this->getParameter('password_' . $param) . "@" . $this->getParameter('hostname_' . $param) . "/admin/api/" . $this->getParameter('version') . "/orders.json?financial_status=paid&fulfillment_status=unfulfilled&limit=250&since_id=".$since_id;
        $response = $client->request('GET', $url);
        $responses =  $response->toArray()['orders'];
        $count=count($response->toArray()['orders']);
        return array($responses, $count);
    }

    public function getSiteCurrentOrders($site) {
        $since_id = 0;
        $ordersRaw = $this->getAllInvoices($site, $since_id);
        $orders = $ordersRaw[0];
        $count = $ordersRaw[1];
        $since_id = $orders[$count-1]['id'];
        if($since_id && $count>=250){
            $result = $this->getAllInvoices($site, $since_id);
            $allInvoices = $result['0'];
            $count = $result['1'];
            $orders = array_merge($allInvoices, $orders);
        }
        return $orders;
    }
}
