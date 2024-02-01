<?php

namespace App\Command;

use Doctrine\Persistence\ManagerRegistry;
use Psr\Log\NullLogger;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Psr\Container\ContainerInterface;
use App\Controller\CaSkuController;
use Symfony\Component\HttpClient\HttpClient;
use App\Entity\CaSku;
use App\Entity\Stores;
use Symfony\Component\Console\Input\InputArgument;

class CaSkuCommand extends Command
{
    // the name of the command (the part after "bin/console")
    protected static $defaultName = 'app:all-ca-sku';

    private $container;
    private $CaSkuController;

    private $doctrine;

    public function __construct(
        ContainerInterface $container,
        CaSkuController $CaSkuController,
        ManagerRegistry $doctrine
    ) {
        parent::__construct();
        $this->container = $container;
        $this->CaSkuController = $CaSkuController;
        $this->doctrine = $doctrine;
    }
    protected function configure(): void
    {
        $this->addArgument('store', InputArgument::REQUIRED, 'store');
        $this->addArgument('sinceid', InputArgument::OPTIONAL, 'since_id');;
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $store = $input->getArgument('store');
        $sinceId = $input->getArgument('sinceid');

        $storeId = $this->container->get('doctrine')->getRepository(Stores::class)->findOneByName($store)->getId();
        if ($sinceId != null) {
            $lastInvoice = 0;
        } else {
            $lastInvoice =  $this->container->get('doctrine')->getRepository(CaSku::class)->findOneBy(['store' => $storeId], array('id' => 'desc'), 1, 0);
            if ($lastInvoice) {
                $lastInvoice = $lastInvoice->getsinceId();
            } else {
                $lastInvoice = 0;
            }
        }
        $entityManager = $this->doctrine->getManager();
        $result = $this->CaSkuController->getAllInvoices($store, $lastInvoice);
        $allInvoices = $result['0'];
        $i = 0;
        if ($allInvoices) {
            foreach ($allInvoices as $saleInvoice) {
                $invoice = $this->container->get('doctrine')->getRepository(CaSku::class)->findOneBy(['sinceId' => $saleInvoice['id']]);
                if (!$invoice) {
                    foreach ($saleInvoice['line_items'] as $items) {
                        $i++;
                        $newSaleInvoice = new CaSku();
                        $newSaleInvoice->setSinceId($saleInvoice['id']);
                        $newSaleInvoice->setNumCommande($saleInvoice['name']);
                        $newSaleInvoice->setNumFacture($saleInvoice['name']);
                        $newSaleInvoice->setStore($storeId);
                        $newSaleInvoice->setDateFacture($saleInvoice['created_at']);
                        if (isset($saleInvoice['billing_address'])) {
                            $newSaleInvoice->setNomClient($saleInvoice['billing_address']['first_name'] . " " . $saleInvoice['billing_address']['last_name']);
                        }
                        if (isset($saleInvoice['default_address'])) {
                            $newSaleInvoice->setNomClient($saleInvoice['default_address']['first_name'] . " " . $saleInvoice['default_address']['last_name']);
                        }
                        $tax = 0;
                        foreach ($saleInvoice['tax_lines'] as $tax) {
                            $tax = $tax['rate'];
                        }

                        $newSaleInvoice->setProductId($items['product_id'] ?: '');
                        $newSaleInvoice->setSku($items['sku']);
                        $newSaleInvoice->setName($items['name']);
                        $newSaleInvoice->setQty($items['quantity']);
                        $shipping = $saleInvoice['total_shipping_price_set']['shop_money']['amount'] / (1 + $tax);
                        $discount = $saleInvoice['total_discounts'] / (1 + $tax);
                        $subTotal =  $items['price'] / (1 + $tax);
                        $newSaleInvoice->setSubtotal($subTotal);
                        $newSaleInvoice->setSubtotalTax($subTotal * ($tax + 1));
                        $newSaleInvoice->setTax($saleInvoice['current_total_tax']);
                        $newSaleInvoice->setDiscount(-$saleInvoice['total_discounts']);
                        if (isset($saleInvoice['billing_address'])) {
                            $newSaleInvoice->setBillingCompany($saleInvoice['billing_address']['company']);
                        }
                        if (isset($saleInvoice['shipping_address'])) {
                            $newSaleInvoice->setShippingCompany($saleInvoice['shipping_address']['company']);
                        }
                        if (($storeId == 23) || ($storeId == 24) || ($storeId == 25)) {
                            $newSaleInvoice->setIsVisible(1);
                        } else {
                            $newSaleInvoice->setIsVisible(0);
                        }
                        $entityManager->persist($newSaleInvoice);
                        $entityManager->flush();
                    }
                } else {
                    echo 'Order ' . $saleInvoice['name'] . ' already in database. Shipped !' . "\n";
                }
            }
        }

        $count = $result['1'];
        if (!$count) {
            $output->writeln('no invoices ' . $store);
        } else {
            $output->writeln($i . ' / ' . $count . ' : new invoices ' . $store);
        }
    }
}
