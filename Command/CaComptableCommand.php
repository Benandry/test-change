<?php

namespace App\Command;

use Doctrine\Persistence\ManagerRegistry;
use Psr\Log\NullLogger;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Psr\Container\ContainerInterface;
use App\Controller\CaComptableController;
use Symfony\Component\HttpClient\HttpClient;
use App\Entity\CaComptable;
use App\Entity\Stores;
use Symfony\Component\Console\Input\InputArgument;

class CaComptableCommand extends Command
{
    // the name of the command (the part after "bin/console")
    protected static $defaultName = 'app:all-ca-comptable';

    private $container;
    private $CaComptableController;

    private $doctrine;

    public function __construct(
        ContainerInterface $container,
        CaComptableController $CaComptableController,
        ManagerRegistry $doctrine
    ) {
        parent::__construct();
        $this->container = $container;
        $this->CaComptableController = $CaComptableController;
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
            $lastInvoice =  $this->container->get('doctrine')->getRepository(CaComptable::class)->findOneBy(['store' => $storeId], array('id' => 'desc'), 1, 0);
            if ($lastInvoice) {
                $lastInvoice = $lastInvoice->getsinceId();
            } else {
                $lastInvoice = 0;
            }
        }
        $entityManager = $this->doctrine->getManager();
        $result = $this->CaComptableController->getAllInvoices($store, $lastInvoice);
        $allInvoices = $result['0'];
        $i = 0;
        if ($allInvoices) {
            foreach ($allInvoices as $saleInvoice) {
                $invoice = $this->container->get('doctrine')->getRepository(CaComptable::class)->findOneBy(['sinceId' => $saleInvoice['id']]);
                if (!$invoice) {
                    $i++;
                    $newSaleInvoice = new CaComptable();
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
                    $newSaleInvoice->setEmailClient($saleInvoice['email']);
                    $preTaxPrice = 0;
                    $qty = 0;


                    foreach ($saleInvoice['line_items'] as $lineItems) {
                        if (isset($lineItems['pre_tax_price'])) {
                            $preTaxPrice = $preTaxPrice + $lineItems['pre_tax_price'];
                            $qty = $qty + $lineItems['quantity'];
                        } else {
                            if (($storeId == 23) || ($storeId == 24) || ($storeId == 25)) {
                                $preTaxPrice = $preTaxPrice + $lineItems['price'];
                            }
                            else {
                                $preTaxPrice = $preTaxPrice;
                            }
                        }
                    }


                    $tax = 0;
                    foreach ($saleInvoice['tax_lines'] as $tax) {
                        $tax = $tax['rate'];
                    }
                    $newSaleInvoice->setQty($qty);
                    $newSaleInvoice->setMethod($saleInvoice['gateway']);
                    $gift = 0;
                    if ($saleInvoice['gateway'] == 'gift_card') {
                        $gift = $saleInvoice['subtotal_price'];
                    }

                    $shipping = $saleInvoice['total_shipping_price_set']['shop_money']['amount'] / (1 + $tax);
                    $newSaleInvoice->setShipping($shipping);
                    $shippingTax = $shipping * ($tax + 1) - $shipping;
                    $newSaleInvoice->setShippingTax($shippingTax);
                    $newSaleInvoice->setGrandTotal($saleInvoice['subtotal_price'] + $shipping + $shippingTax);
                    $discount = $saleInvoice['total_discounts'] / (1 + $tax);
                    $newSaleInvoice->setShippinInclTax($shippingTax + $shipping);
                    $newSaleInvoice->setCountryId($saleInvoice['shipping_address']['country_code']);
                    if (($storeId == 23) || ($storeId == 24) || ($storeId == 25)) {
                        $subTotal = $preTaxPrice;
                        $newSaleInvoice->setIsVisible(1);
                    } else {
                        $subTotal = $preTaxPrice + $discount;
                        $newSaleInvoice->setIsVisible(0);
                    }
                    $newSaleInvoice->setSubtotal($subTotal);
                    $newSaleInvoice->setSubtotalTax($subTotal * ($tax + 1));
                    $newSaleInvoice->setTax($tax * ($shipping + $subTotal));
                    $newSaleInvoice->setDiscount(-$saleInvoice['total_discounts']);
                    if (($storeId == 23) || ($storeId == 24) || ($storeId == 25)) {
                        $newSaleInvoice->setIsVisible(1);
                    } else {
                        $newSaleInvoice->setIsVisible(0);
                    }
                    $entityManager->persist($newSaleInvoice);
                    $entityManager->flush();
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
