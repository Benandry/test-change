<?php

namespace App\Command;

use Doctrine\Persistence\ManagerRegistry;
use Psr\Log\NullLogger;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Psr\Container\ContainerInterface;
use App\Controller\SalesInvoiceController;
use Symfony\Component\HttpClient\HttpClient;
use App\Entity\ListSalesInvoices;
use App\Entity\Stores;
use Symfony\Component\Console\Input\InputArgument;

class SalesInvoiceCommand extends Command
{
    // the name of the command (the part after "bin/console")
    protected static $defaultName = 'app:all-sales-invoices';

    private $container;
    private $salesInvoiceController;

    private $doctrine;

    public function __construct(
        ContainerInterface $container, 
        SalesInvoiceController $salesInvoiceController,
        ManagerRegistry $doctrine)
    {
        parent::__construct();
        $this->container = $container;
        $this->SalesInvoiceController = $salesInvoiceController;
        $this->doctrine = $doctrine;
    }
    protected function configure(): void
    {
        $this->addArgument('store', InputArgument::REQUIRED, 'store');
        $this->addArgument('sinceid', InputArgument::OPTIONAL, 'since_id');
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $store=$input->getArgument('store');
		$sinceId = $input->getArgument('sinceid');
        $storeId = $this->container->get('doctrine')->getRepository(Stores::class)->findOneByName($store)->getId();
        $entityManager = $this->doctrine->getManager();
        if($sinceId=='all') {
            $result = $this->SalesInvoiceController->getAllorders($store, 0);
            $allInvoices = $result['0'];
            $allCount = $count = $result['1'];
            $output->writeln($count . ' orders found! Need to fetch more ?');
            $saved = $this->saveData($allInvoices, $storeId);
            $lastId = $allInvoices[$count-1]['id'];
            $allInvoices = null;
            while($count>0 && $count%250==0) {
                $nexts = $this->SalesInvoiceController->getAllorders($store, $lastId);
                $nextOrders = $nexts[0];
                $count = $nexts['1'];
                $output->writeln($count . ' more from ' . $lastId . '...');
                $allCount += $count;
                $nexts = null;
                $lastId = $nextOrders[$count-1]['id'];
                $saved += $this->saveData($nextOrders, $storeId);
            }
        }else {
            $result = $this->SalesInvoiceController->getAllInvoices($store, $sinceId);
            $allInvoices = $result['0'];
            $allCount = $count = $result['1'];
            $output->writeln($count . ' orders found! Need to fetch more ?');
            $saved = $this->saveData($allInvoices, $storeId);
            $lastId = $allInvoices[$count-1]['id'];
            $allInvoices = null;
            while($count>0 && $count%250==0) {
                $nexts = $this->SalesInvoiceController->getNextOrders($store, $lastId);
                $nextOrders = $nexts[0];
                $count = $nexts['1'];
                $output->writeln($count . ' more from ' . $lastId . '...');
                $allCount += $count;
                $nexts = null;
                $lastId = $nextOrders[$count-1]['id'];
                $saved += $this->saveData($nextOrders, $storeId);
            }
        }
        if(!$count) {
            $output->writeln('no sales invoices '. $store);
        } else {
           $output->writeln($saved . ' / ' . $allCount . ' : new sales invoices from '. $store. ' store');
        }
    }

    protected function saveData($datas, $storeId) {
        if ($datas) {
            $entityManager = $this->doctrine->getManager();
            $i = 0;
            foreach ($datas as $saleInvoice) {
                $invoice = $this->container->get('doctrine')->getRepository(ListSalesInvoices::class)->findOneBy(['sinceId' => $saleInvoice['id']]);
                if (!$invoice) {
                    $i++;
                    $newSaleInvoice = new ListSalesInvoices();
                    $newSaleInvoice->setSinceId($saleInvoice['id']);
                    $newSaleInvoice->setNumCommande($saleInvoice['name']);
                    $newSaleInvoice->setNumFacture($saleInvoice['name']);
                    $newSaleInvoice->setStatus($saleInvoice['financial_status']);
                    $newSaleInvoice->setStore($storeId);
                    $newSaleInvoice->setDateCommande(date('Y-m-d H:i:s', strtotime($saleInvoice['created_at'])));
                    $newSaleInvoice->setDateFacture(date('Y-m-d H:i:s', strtotime($saleInvoice['created_at'])));
                    if (is_null($saleInvoice['user_id'])) {
                        if (isset($saleInvoice['customer'])) {
                            $newSaleInvoice->setIdClient($saleInvoice['customer']['id']);
                        }
                    } else {
                        $newSaleInvoice->setIdClient($saleInvoice['user_id']);
                    }
                    if (isset($saleInvoice['billing_address'])) {
                        $newSaleInvoice->setNomClient($saleInvoice['billing_address']['first_name'] . " " . $saleInvoice['billing_address']['last_name']);
                        $newSaleInvoice->setCompany($saleInvoice['billing_address']['company']);
                    }  if (isset($saleInvoice['default_address'])) {
                        $newSaleInvoice->setNomClient($saleInvoice['default_address']['first_name'] . " " . $saleInvoice['default_address']['last_name']);
                        $newSaleInvoice->setCompany($saleInvoice['default_address']['company']);
                    }
                    $newSaleInvoice->setCountryCode($saleInvoice['shipping_address']['country_code']);
                    $newSaleInvoice->setEmailClient($saleInvoice['email']);
                    $preTaxPrice=0;
                    foreach ($saleInvoice['fulfillments'] as $fulfillments) {
                        foreach ($fulfillments['line_items'] as $lineItems)
                            $preTaxPrice= $preTaxPrice + $lineItems['pre_tax_price'];
                    }
                    $tax = 0;
                    if(isset($saleInvoice['tax_lines'][0])){
                        $tax = $saleInvoice['tax_lines'][0]['rate'];
                    }
                    $newSaleInvoice->setShipping($saleInvoice['total_shipping_price_set']['shop_money']['amount']/(1+$tax));
                    $discount=$saleInvoice['total_discounts']/(1+$tax);
                    $newSaleInvoice->setSubtotal($preTaxPrice+$discount);
                    $newSaleInvoice->setDiscount($discount);
                    $newSaleInvoice->setIsVisible(0);

                    $entityManager->persist($newSaleInvoice);
                    $entityManager->flush();
                }else{
					//echo 'Order ' . $saleInvoice['name'] . ' already in database. Skipped !' . "\n";
				}
            }
            return $i;
        }
    }
}