<?php

namespace App\Command;

use Doctrine\Persistence\ManagerRegistry;
use Psr\Log\NullLogger;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Psr\Container\ContainerInterface;
use App\Controller\TvaInvoiceController;
use Symfony\Component\HttpClient\HttpClient;
use App\Entity\ListTvaInvoices;
use App\Entity\Stores;
use Symfony\Component\Console\Input\InputArgument;

class TvaInvoiceCommand extends Command
{
    // the name of the command (the part after "bin/console")
    protected static $defaultName = 'app:all-tva-invoices';

    private $container;
    private $tvaCreditmemoController;

    private $doctrine;

    public function __construct(
        ContainerInterface $container,
        TvaInvoiceController $tvaCreditmemoController,
        ManagerRegistry $doctrine)
    {
        parent::__construct();
        $this->container = $container;
        $this->TvaInvoiceController = $tvaCreditmemoController;
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
            $result = $this->TvaInvoiceController->getAllorders($store, 0);
            $allInvoices = $result['0'];
            $allCount = $count = $result['1'];
            $output->writeln($count . ' orders found! Need to fetch more ?');
            $saved = $this->saveData($allInvoices, $storeId);
            $lastId = $allInvoices[$count-1]['id'];
            $allInvoices = null;
            while($count>0 && $count%250==0) {
                $nexts = $this->TvaInvoiceController->getAllorders($store, $lastId);
                $nextOrders = $nexts[0];
                $count = $nexts['1'];
                $output->writeln($count . ' more from ' . $lastId . '...');
                $allCount += $count;
                $nexts = null;
                $lastId = $nextOrders[$count-1]['id'];
                $saved += $this->saveData($nextOrders, $storeId);
            }
        }else {
            $result = $this->TvaInvoiceController->getAllTvaInvoices($store, $sinceId);
            $allInvoices = $result['0'];
            $allCount = $count = $result['1'];
            $output->writeln($count . ' orders found! Need to fetch more ?');
            $saved = $this->saveData($allInvoices, $storeId);
            $lastId = $allInvoices[$count-1]['id'];
            $allInvoices = null;
            $i = 1;
            while($count>0 && $count%250==0 && $i<3) {
                $nexts = $this->TvaInvoiceController->getNextOrders($store, $lastId);
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
                $invoice = $this->container->get('doctrine')->getRepository(ListTvaInvoices::class)->findOneBy(['sinceId' => $saleInvoice['id']]);
                if (!$invoice) {
                    $i++;
                    $newSaleInvoice = new ListTvaInvoices();
                    $newSaleInvoice->setSinceId($saleInvoice['id']);
                    $newSaleInvoice->setNumCommande($saleInvoice['name']);
                    $newSaleInvoice->setNumFacture($saleInvoice['name']);
                    $newSaleInvoice->setStore($storeId);
                    $newSaleInvoice->setDateFacture(date('Y-m-d H:i:s', strtotime($saleInvoice['created_at'])));

                    if (isset($saleInvoice['billing_address'])) {
                        $newSaleInvoice->setCompany($saleInvoice['billing_address']['company']);
                    } if (isset($saleInvoice['default_address'])) {
                        $newSaleInvoice->setCompany($saleInvoice['default_address']['company']);
                    }if (isset($saleInvoice['shipping_address'])) {
                        $newSaleInvoice->setCountryCode($saleInvoice['shipping_address']['country_code']);
                    }
                    $newSaleInvoice->setTotalTtc($saleInvoice['total_price']);
                    $newSaleInvoice->setMontantTaxe($saleInvoice['total_tax']);

                    $preTaxPrice=0;
                    foreach ($saleInvoice['fulfillments'] as $fulfillments) {
                        foreach ($fulfillments['line_items'] as $lineItems)
                            $preTaxPrice= $preTaxPrice + $lineItems['pre_tax_price'];
                    }
                    $tax = 0;
                    if(isset($saleInvoice['tax_lines'][0])){
                        $tax = $saleInvoice['tax_lines'][0]['rate'];
                    }
                    $shipping=$saleInvoice['total_shipping_price_set']['shop_money']['amount']/(1+$tax);
                    $newSaleInvoice->setShipping($shipping);
                    $discount=$saleInvoice['total_discounts']/(1+$tax);
                    $newSaleInvoice->setSubtotal($preTaxPrice+$discount);
                    $newSaleInvoice->setDiscount($discount);
                    $newSaleInvoice->setTaxeLivraison($shipping-($shipping/(1+$tax)));


                    if(($saleInvoice['total_discounts']!="0.00") && ($saleInvoice['total_line_items_price']!="0.00")) {
                        $poucentremise =($saleInvoice['total_discounts'] / $saleInvoice['total_line_items_price'])*100;
                        $newSaleInvoice->setDiscountPercent(round($poucentremise));
                    }
                    else {
                        $newSaleInvoice->setDiscountPercent('0.00');
                    }

                    foreach ($saleInvoice['tax_lines'] as $tax) {
                        $tva20='0.00';
                        $baseHt20='0.00';
                        $tva55='0.00';
                        $baseHt55='0.00';
                        $tva0='0.00';
                        $baseHt0='0.00';
                        $tva21='0.00';
                        $baseHt21='0.00';
                        $tva6='0.00';
                        $baseHt6='0.00';
                        $tvaMulti='0.00';
                        $baseHtMulti='0.00';

                        if ($tax['rate'] == 0.2) {
                            $tva20=$tax['price'];
                            $baseHt20= $saleInvoice['total_price'] - $tax['price'];
                        }
                        if ($tax['rate'] == 0.055) {
                            $tva55=$tax['price'];
                            $baseHt55=$saleInvoice['total_price'] - $tax['price'];
                        }
                         if ($tax['rate'] == 0) {
                            $tva0=$tax['price'];
                            $baseHt0=$saleInvoice['total_price'] - $tax['price'];
                        }
                         if ($tax['rate'] == 0.21) {
                            $tva21=$tax['price'];
                            $baseHt21=$saleInvoice['total_price'] - $tax['price'];
                        }
                         if ($tax['rate'] == 0.06) {
                            $tva6=$tax['price'];
                            $baseHt6=$saleInvoice['total_price'] - $tax['price'];
                        }

                        $newSaleInvoice->setTva20($tva20);
                        $newSaleInvoice->setBaseHt20($baseHt20);
                        $newSaleInvoice->setTva55($tva55);
                        $newSaleInvoice->setBaseHt55($baseHt55);
                        $newSaleInvoice->setTva0($tva0);
                        $newSaleInvoice->setBaseHt0($baseHt0);
                        $newSaleInvoice->setTva21($tva21);
                        $newSaleInvoice->setBaseHt21($baseHt21);
                        $newSaleInvoice->setTva6($tva6);
                        $newSaleInvoice->setBaseHt6($baseHt6);
                        $newSaleInvoice->setTvaMulti($tvaMulti);
                        $newSaleInvoice->setBaseHtMulti($baseHtMulti);
                        $ecart=$tva20+$baseHt20+$tva55+$baseHt55+$tva0+$baseHt0+$tva21+$baseHt21+$tva6+$tvaMulti+$baseHtMulti+$baseHt6-$saleInvoice['total_price'] ;
                        $newSaleInvoice->setEcart($ecart);
                        $newSaleInvoice->setTauxTaxe($tax['rate']*100);
                        $newSaleInvoice->setIsVisible(0);
                    }
                    try {
                    $entityManager->persist($newSaleInvoice);
                    $entityManager->flush();
                    }catch(\Exception $e){
                        //var_dump($saleInvoice['name']); exit;
                    }
                }
            }
            return $i;
        }
    }
}