<?php

namespace App\Command;

use App\Controller\UfcOrderController;
use App\Entity\Stores;
use App\Entity\UfcOrders;
use Doctrine\Persistence\ManagerRegistry;
use Psr\Container\ContainerInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

class UfcOrderCommand extends Command
{
    protected static $defaultName = 'app:ufc-order';
    protected static $defaultDescription = 'get all UFC ORDER';
    private $container;
    private $ufcOrderController;
    private $doctrine;


    public function __construct(
        ContainerInterface $container,
        UfcOrderController $ufcOrderController,
        ManagerRegistry $doctrine
    ) {
        parent::__construct();
        $this->container = $container;
        $this->ufcOrderController = $ufcOrderController;
        $this->doctrine = $doctrine;
    }

    protected function configure(): void
    {
        $this->addArgument('store', InputArgument::REQUIRED, 'store');
        $this->addArgument('sinceid', InputArgument::OPTIONAL, 'since_id');;
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);

        $store = $input->getArgument('store');
        $sinceId = $input->getArgument('sinceid');

        $storeId = $this->container->get('doctrine')->getRepository(Stores::class)->findOneByName($store)->getId();
        if ($sinceId != null) {
            $lastInvoice = 0;
        } else {
            $lastInvoice =  $this->container->get('doctrine')->getRepository(UfcOrders::class)->findOneBy(['store' => $storeId], array('id' => 'desc'), 1, 0);
            if ($lastInvoice) {
                $lastInvoice = $lastInvoice->getsinceId();
            } else {
                $lastInvoice = 0;
            }
        }

        $entityManager = $this->doctrine->getManager();
        $result = $this->ufcOrderController->getAllUfcOrder($store, $lastInvoice);
        $allUfcOrder = $result['0'];

        if ($allUfcOrder) {
            foreach ($allUfcOrder as $ufc_order) {
                $order = $this->container->get('doctrine')->getRepository(UfcOrders::class)->findOneBy(['sinceId' => $ufc_order['id']]);
                $i = 0;
                if (!$order) {
                    foreach ($ufc_order['line_items'] as $items) {
                        if (strpos($items['sku'], 'VNMUFC') === 0) {
                            $i++;
                            $new_ufc_order = new UfcOrders();
                            $new_ufc_order->setSinceId($ufc_order['id']);
                            $new_ufc_order->setNumCommande($ufc_order['name']);
                            $new_ufc_order->setNumFacture($ufc_order['name']);
                            $new_ufc_order->setStore($storeId);
                            $new_ufc_order->setDateFacture($ufc_order['created_at']);

                            if (isset($ufc_order['billing_address'])) {
                                $new_ufc_order->setNomClient($ufc_order['billing_address']['first_name'] . " " . $ufc_order['billing_address']['last_name']);
                            }
                            if (isset($ufc_order['default_address'])) {
                                $new_ufc_order->setNomClient($ufc_order['default_address']['first_name'] . " " . $ufc_order['default_address']['last_name']);
                            }
                            $preTaxPrice = 0;
                            $qty = 0;

                            if (isset($items['pre_tax_price'])) {
                                $preTaxPrice = $preTaxPrice + $items['pre_tax_price'];
                                $qty = $qty + $items['quantity'];
                            } else {
                                if (($storeId == 23) || ($storeId == 24) || ($storeId == 25)) {
                                    $preTaxPrice = $preTaxPrice + $items['price'];
                                } else {
                                    $preTaxPrice = $preTaxPrice;
                                }
                                $qty = $qty + $items['quantity'];
                            }

                            $tax = 0;
                            foreach ($ufc_order['tax_lines'] as $tax) {
                                $tax = $tax['rate'];
                            }

                            $new_ufc_order->setProductId($items['product_id'] ?? '');
                            $new_ufc_order->setSku($items['sku']);
                            $new_ufc_order->setName($items['name']);
                            $new_ufc_order->setQty($items['quantity']);
                            $shipping = $ufc_order['total_shipping_price_set']['shop_money']['amount'] / (1 + $tax);
                            $discount = $ufc_order['total_discounts'] / (1 + $tax);
                            $subTotal =  $items['price'] / (1 + $tax);
                            $new_ufc_order->setSubtotal($subTotal);
                            $new_ufc_order->setSubtotalTax($subTotal * ($tax + 1));
                            $new_ufc_order->setTax($ufc_order['current_total_tax']);
                            $new_ufc_order->setDiscount(-$ufc_order['total_discounts']);
                            if (isset($ufc_order['billing_address'])) {
                                $new_ufc_order->setBillingCompany($ufc_order['billing_address']['company']);
                            }
                            if (isset($ufc_order['shipping_address'])) {
                                $new_ufc_order->setShippingCompany($ufc_order['shipping_address']['company']);
                            }
                            if (($storeId == 23) || ($storeId == 24) || ($storeId == 25)) {
                                $new_ufc_order->setIsVisible(1);
                            } else {
                                $new_ufc_order->setIsVisible(0);
                            }
                            $entityManager->persist($new_ufc_order);
                            $entityManager->flush();
                        }
                    }
                } else {
                    echo 'Order ' . $ufc_order['name'] . ' already in database. Shipped !' . "\n";
                }
            }
        }


        $count = $result['1'];
        if (!$count) {
            $io->warning("No orders" . $store);
        } else {
            $io->success($i . ' / ' . $count . ' : new orders ' . $store);
        }


        return 0;
    }
}
