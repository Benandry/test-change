<?php

namespace App\Command;

use Doctrine\Persistence\ManagerRegistry;
use Psr\Log\NullLogger;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Psr\Container\ContainerInterface;
use App\Controller\SalesCreditMemoController;
use Symfony\Component\HttpClient\HttpClient;
use App\Entity\ListSaleCreditMemo;
use App\Entity\Stores;
use Symfony\Component\Console\Input\InputArgument;

class SalesCreditMemoCommand extends Command
{
    // the name of the command (the part after "bin/console")
    protected static $defaultName = 'app:all-sales-creditmemo';

    private $container;
    private $salesCreditMemoController;

    private $doctrine;

    public function __construct(
        ContainerInterface $container,
        SalesCreditMemoController $salesCreditMemoController,
        ManagerRegistry $doctrine)
    {
        parent::__construct();
        $this->container = $container;
        $this->SalesCreditMemoController = $salesCreditMemoController;
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
        
        
        $result = $this->SalesCreditMemoController->getAllCreditmemeo($store, $sinceId);

        $allInvoices = $result['0'];

        $saved = $this->saveData($allInvoices, $storeId);
        

        $count = $result['1'];
        if(!$count) {
            $output->writeln('no Creditmemo');
        } else {
            $output->writeln($saved . ' / ' . $count . ' : new Creditmemo');
        }
    }

    protected function getShippingRefunded($refund, $tax) {
        $totalShipping = 0;
        $totalShippingTax = 0;
        if(isset($refund['order_adjustments']) && !empty($refund['order_adjustments'])) {
            $refundLines = $refund['order_adjustments'];
            foreach($refundLines as $refundLine) {
                $itemPriceHt = abs($refundLine['amount']);
                $itemRowTotalHt = abs($refundLine['amount']);
                $itemRowTotalTtc = abs($refundLine['amount'] * (1 + $tax));
                $montantTva = $itemRowTotalTtc - $itemRowTotalHt;
                $totalShipping =+ $itemRowTotalHt;
                $totalShippingTax =+ $montantTva;
            }
        }
        return array('amount'=>$totalShipping, 'tax_amount'=>$totalShippingTax);
    }

    protected function getRefundsTransactionsAmount($refund) {
        $totalTransactionAmount = 0;
        if(isset($refund['transactions']) && !empty($refund['transactions'])) {
            $transactions = $refund['transactions'];
            foreach($transactions as $transaction) {
                $totalTransactionAmount =+  $transaction['amount'];
            }
        }
        return $totalTransactionAmount;
    }

    protected function saveData($datas, $storeId) {
        $i = 0;
        if ($datas) {
            $entityManager = $this->doctrine->getManager();
            foreach ($datas as $saleInvoice) {
                if(isset($saleInvoice['refunds']) && !empty($saleInvoice['refunds'])) {
                    $refunds = $saleInvoice['refunds'];
                    foreach($refunds as $refund) {
                        $invoice = $this->container->get('doctrine')->getRepository(ListSaleCreditMemo::class)->findOneBy(['refundId' => $refund['id']]);
                        if (!$invoice) {
                            $i++;
                            $newSaleInvoice = new ListSaleCreditMemo();
                            $newSaleInvoice->setSinceId($saleInvoice['id']);
                            $newSaleInvoice->setNumCommande($saleInvoice['name']);
                            $newSaleInvoice->setNumCreditmemo($saleInvoice['order_number']);
                            $newSaleInvoice->setStatus($saleInvoice['financial_status']);
                            $newSaleInvoice->setStore($storeId);
                            $newSaleInvoice->setRefundId($refund['id']);
                            $newSaleInvoice->setDateCommande(date('Y-m-d H:i:s', strtotime($saleInvoice['created_at'])));
                            $newSaleInvoice->setDateCreditmemo(date('Y-m-d H:i:s', strtotime($refund['created_at'])));
                            if (is_null($saleInvoice['user_id'])) {
                                if (isset($saleInvoice['customer'])) {
                                    $newSaleInvoice->setIdClient($saleInvoice['customer']['id']);
                                }
                            } else {
                                $newSaleInvoice->setIdClient($saleInvoice['user_id']);
                            }
                            if ($saleInvoice['billing_address']) {
                                $newSaleInvoice->setNomClient($saleInvoice['billing_address']['first_name'] . " " . $saleInvoice['billing_address']['last_name']);
                                $newSaleInvoice->setCompany($saleInvoice['billing_address']['company']);
                            } else {
                                $newSaleInvoice->setNomClient($saleInvoice['default_address']['first_name'] . " " . $saleInvoice['default_address']['last_name']);
                                $newSaleInvoice->setCompany($saleInvoice['default_address']['company']);
                            }
                            $newSaleInvoice->setCountryCode($saleInvoice['shipping_address']['country_code']);
                            $newSaleInvoice->setEmailClient($saleInvoice['email']);
                            $tax = $saleInvoice['tax_lines'][0]['rate'];
                            $subtotalRefunded = round($this->getRefundsTransactionsAmount ($refund),2);
                            $shippings = $this->getShippingRefunded ($refund, $tax);
                            $shipping=$shippings['amount'];
                            $discount=0;

                            $newSaleInvoice->setShipping($shipping/(1+$tax));
                            $newSaleInvoice->setSubtotal($subtotalRefunded/(1+$tax));
                            $newSaleInvoice->setDiscount($discount);
                            $newSaleInvoice->setIsVisible(0);

                            $entityManager->persist($newSaleInvoice);
                            $entityManager->flush();
                        }
                    }
                }
            }
        }
        return $i;
    }
}