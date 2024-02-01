<?php

namespace App\Command;

use Doctrine\Persistence\ManagerRegistry;
use Psr\Log\NullLogger;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Psr\Container\ContainerInterface;
use App\Controller\CurrentOrdersController;
use Symfony\Component\HttpClient\HttpClient;
use App\Entity\ListCrurrentOrders;
use App\Entity\Stores;
use Symfony\Component\Console\Input\InputArgument;

class SalesCurrentOrderCommand extends Command
{
    // the name of the command (the part after "bin/console")
    protected static $defaultName = 'app:all-current-order';

    private $container;
    private $currentOrdersController;

    private $doctrine;

    public function __construct(
        ContainerInterface $container,
        CurrentOrdersController $currentOrdersController,
        ManagerRegistry $doctrine)
    {
        parent::__construct();
        $this->container = $container;
        $this->CurrentOrdersController = $currentOrdersController;
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
        $storeId = $this->container->get('doctrine')->getRepository(Stores::class)->findOneByName($store)->getId();
        $lastInvoice=  $this->container->get('doctrine')->getRepository(ListCrurrentOrders::class)->findOneBy(['store' => $storeId], array('id' => 'desc'),1,0);

        if($lastInvoice) {
            $lastInvoice=$lastInvoice->getsinceId();
        }
        else {
            $lastInvoice=0;
        }
        $entityManager = $this->doctrine->getManager();
        $result = $this->CurrentOrdersController->getAllCurrentOrders($store, $lastInvoice);

        $allInvoices = $result['0'];

        $i=0;
        if ($allInvoices) {
            foreach ($allInvoices as $saleInvoice) {
                $invoice = $this->container->get('doctrine')->getRepository(ListCrurrentOrders::class)->findOneBy(['sinceId' => $saleInvoice['id']]);
                if (!$invoice) {
                    $i++;
                    $newSaleInvoice = new ListCrurrentOrders();
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
                    if ($saleInvoice['billing_address']) {
                        $newSaleInvoice->setNomClient($saleInvoice['billing_address']['first_name'] . " " . $saleInvoice['billing_address']['last_name']);
                        $newSaleInvoice->setCountryCode($saleInvoice['billing_address']['country_code']);
                        $newSaleInvoice->setCompany($saleInvoice['billing_address']['company']);
                    } else {
                        $newSaleInvoice->setNomClient($saleInvoice['default_address']['first_name'] . " " . $saleInvoice['default_address']['last_name']);
                        $newSaleInvoice->setCountryCode($saleInvoice['default_address']['country_code']);
                        $newSaleInvoice->setCompany($saleInvoice['default_address']['company']);
                    }
                    $newSaleInvoice->setEmailClient($saleInvoice['email']);
                    $newSaleInvoice->setSubtotal($saleInvoice['total_line_items_price'] - $saleInvoice['total_tax'] + $saleInvoice['total_shipping_price_set']['shop_money']['amount'] - $saleInvoice['total_discounts']);
                    $newSaleInvoice->setShipping($saleInvoice['total_shipping_price_set']['shop_money']['amount']);
                    $newSaleInvoice->setDiscount($saleInvoice['total_discounts']);

                    $entityManager->persist($newSaleInvoice);
                    $entityManager->flush();
                }
            }
        }

        $count = $result['1'];
        if(!$count) {
            $output->writeln('no current orders');
        } else {
            $output->writeln($i . ' / ' . $count . ' : new current orders');
        }
    }
}