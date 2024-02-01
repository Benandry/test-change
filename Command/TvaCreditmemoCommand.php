<?php

namespace App\Command;

use Doctrine\Persistence\ManagerRegistry;
use Psr\Log\NullLogger;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Psr\Container\ContainerInterface;
use App\Controller\TvaCreditmemoController;
use Symfony\Component\HttpClient\HttpClient;
use App\Entity\ListTvaCreditmemo;
use App\Entity\Stores;
use Symfony\Component\Console\Input\InputArgument;

class TvaCreditmemoCommand extends Command
{
    // the name of the command (the part after "bin/console")
    protected static $defaultName = 'app:all-tva-creditmemo';

    private $container;
    private $tvaCreditmemoController;

    private $doctrine;

    public function __construct(
        ContainerInterface $container,
        TvaCreditmemoController $tvaCreditmemoController,
        ManagerRegistry $doctrine
    ) {
        parent::__construct();
        $this->container = $container;
        $this->tvaCreditmemoController = $tvaCreditmemoController;
        $this->doctrine = $doctrine;
    }
    protected function configure(): void
    {
        $this->addArgument('store', InputArgument::REQUIRED, 'store');
        $this->addArgument('sinceid', InputArgument::OPTIONAL, 'since_id');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $store = $input->getArgument('store');
        $sinceId = $input->getArgument('sinceid');

        $storeId = $this->container->get('doctrine')->getRepository(Stores::class)->findOneByName($store)->getId();

        $entityManager = $this->doctrine->getManager();
        $result = $this->tvaCreditmemoController->getAllTvaCreditmemo($store, $sinceId);

        $allInvoices = $result['0'];

        $i = 0;
        if ($allInvoices) {
            foreach ($allInvoices as $saleInvoice) {
                if (isset($saleInvoice['refunds']) && !empty($saleInvoice['refunds'])) {
                    $refunds = $saleInvoice['refunds'];
                    foreach ($refunds as $refund) {
                        $invoice = $this->container->get('doctrine')->getRepository(ListTvaCreditmemo::class)->findOneBy(['refundId' => $refund['id']]);
                        if (!$invoice) {
                            $i++;
                            $newSaleInvoice = new ListTvaCreditmemo();
                            $newSaleInvoice->setSinceId($saleInvoice['id']);
                            $newSaleInvoice->setNumCommande($saleInvoice['name']);
                            $newSaleInvoice->setNumFacture($saleInvoice['name']);
                            $newSaleInvoice->setStore($storeId);
                            $newSaleInvoice->setRefundId($refund['id']);
                            $newSaleInvoice->setDateFacture(date('Y-m-d H:i:s', strtotime($refund['created_at'])));

                            if ($saleInvoice['billing_address']) {
                                $newSaleInvoice->setCompany($saleInvoice['billing_address']['company']);
                            } else {
                                $newSaleInvoice->setCompany($saleInvoice['default_address']['company']);
                            }
                            $newSaleInvoice->setCountryCode($saleInvoice['shipping_address']['country_code']);
                            $tax = $saleInvoice['tax_lines'][0]['rate'];
                            $subtotalRefunded = $this->getRefundsTransactionsAmount($refund);
                            $shippings = $this->getShippingRefunded($refund, $tax);
                            $shipping = $shippings['amount'];
                            $discount = 0;
                            $shippingTaxAmount = $shippings['tax_amount'];
                            $discountPercent = 0;
                            $newSaleInvoice->setShipping($shipping);
                            $subtotalRefundedHt = round($subtotalRefunded / (1 + $tax), 2);
                            $newSaleInvoice->setMontantTaxe($subtotalRefunded - $subtotalRefundedHt);
                            $newSaleInvoice->setSubtotal($subtotalRefundedHt);
                            $newSaleInvoice->setTotalTtc($subtotalRefunded);
                            $newSaleInvoice->setDiscount($discount);
                            $newSaleInvoice->setTaxeLivraison($shippingTaxAmount);
                            $newSaleInvoice->setDiscountPercent($discountPercent);
                            foreach ($saleInvoice['tax_lines'] as $tax) {
                                $tva20 = '0.00';
                                $baseHt20 = '0.00';
                                $tva55 = '0.00';
                                $baseHt55 = '0.00';
                                $tva0 = '0.00';
                                $baseHt0 = '0.00';
                                $tva21 = '0.00';
                                $baseHt21 = '0.00';
                                $tva6 = '0.00';
                                $baseHt6 = '0.00';
                                $tvaMulti = '0.00';
                                $baseHtMulti = '0.00';

                                if ($tax['rate'] == 0.2) {
                                    $tva20 = $subtotalRefunded - $subtotalRefundedHt;;
                                    $baseHt20 = $subtotalRefundedHt;
                                }
                                if ($tax['rate'] == 0.055) {
                                    $tva55 = $subtotalRefunded - $subtotalRefundedHt;;
                                    $baseHt55 = $subtotalRefundedHt;
                                }
                                if ($tax['rate'] == 0) {
                                    $tva0 = $subtotalRefunded - $subtotalRefundedHt;;
                                    $baseHt0 = $subtotalRefundedHt;
                                }
                                if ($tax['rate'] == 0.21) {
                                    $tva21 = $subtotalRefunded - $subtotalRefundedHt;
                                    $baseHt21 = $subtotalRefundedHt;
                                }
                                if ($tax['rate'] == 0.06) {
                                    $tva6 = $subtotalRefunded - $subtotalRefundedHt;
                                    $baseHt6 = $subtotalRefundedHt;
                                }
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
                            $ecart = $tva20 + $baseHt20 + $tva55 + $baseHt55 + $tva0 + $baseHt0 + $tva21 + $baseHt21 + $tva6 + $tvaMulti + $baseHtMulti + $baseHt6 - $subtotalRefunded;
                            $newSaleInvoice->setEcart($ecart);
                            $newSaleInvoice->setTauxTaxe($tax['rate'] * 100);
                            $newSaleInvoice->setIsVisible(0);
                            $entityManager->persist($newSaleInvoice);
                            $entityManager->flush();
                        }
                    }
                }
            }
        }

        $count = $result['1'];
        if (!$count) {
            $output->writeln('no Tva CreditMemo ' . $store);
        } else {
            $output->writeln($i . ' / ' . $count . ' : new Tva CreditMemo ' . $store);
        }
    }

    protected function getShippingRefunded($refund, $tax)
    {
        $totalShipping = 0;
        $totalShippingTax = 0;
        if (isset($refund['order_adjustments']) && !empty($refund['order_adjustments'])) {
            $refundLines = $refund['order_adjustments'];
            foreach ($refundLines as $refundLine) {
                $itemPriceHt = abs($refundLine['amount']);
                $itemRowTotalHt = abs($refundLine['amount']);
                $itemRowTotalTtc = abs($refundLine['amount'] * (1 + $tax));
                $montantTva = $itemRowTotalTtc - $itemRowTotalHt;
                $totalShipping = +$itemRowTotalHt;
                $totalShippingTax = +$montantTva;
            }
        }
        return array('amount' => $totalShipping, 'tax_amount' => $totalShippingTax);
    }

    protected function getRefundsTransactionsAmount($refund)
    {
        $totalTransactionAmount = 0;
        if (isset($refund['transactions']) && !empty($refund['transactions'])) {
            $transactions = $refund['transactions'];
            foreach ($transactions as $transaction) {
                $totalTransactionAmount = +$transaction['amount'];
            }
        }
        return $totalTransactionAmount;
    }
}
