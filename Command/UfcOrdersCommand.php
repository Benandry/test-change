<?php

namespace App\Command;

use App\Entity\UfcProducts;
use Doctrine\Persistence\ManagerRegistry;
use Psr\Log\NullLogger;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Psr\Container\ContainerInterface;
use App\Controller\UfcOrdersController;
use Symfony\Component\HttpClient\HttpClient;
use App\Entity\ListUfcOrders;
use App\Entity\Stores;
use Symfony\Component\Console\Input\InputArgument;
use App\Entity\ListSinceId;

class UfcOrdersCommand extends Command
{
    // the name of the command (the part after "bin/console")
    protected static $defaultName = 'app:all-ufc-orders';

    private $container;
    private $salesInvoiceController;

    private $doctrine;

    public function __construct(
        ContainerInterface $container,
        UfcOrdersController $ufcOrdersController,
        ManagerRegistry $doctrine)
    {
        parent::__construct();
        $this->container = $container;
        $this->UfcOrdersController = $ufcOrdersController;
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
        $lastInvoice=  $this->container->get('doctrine')->getRepository(ListSinceId::class)->findOneBy(['storeId' => $storeId , 'list' => 'UfcOrders' ], array('id' => 'desc'),1,0);

        if($lastInvoice) {
            $lastInvoice=$lastInvoice->getsinceId();
        }
        else {
            $lastInvoice=0;
        }
        $entityManager = $this->doctrine->getManager();
        $result = $this->UfcOrdersController->getAllUfcOrders($store, $lastInvoice);

        $allInvoices = $result['0'];

        $i=0;
        if ($allInvoices) {
            foreach ($allInvoices as $saleInvoice) {
                $invoice = $this->container->get('doctrine')->getRepository(ListUfcOrders::class)->findOneBy(['sinceId' => $saleInvoice['id']]);

                if (!$invoice) {

                    foreach ($saleInvoice['line_items'] as $items) {

                        if (($items['properties']) && (strpos($items['sku'], 'VNMUFC') !== false) && ((strpos($items['sku'], '-C') !== false) || (strpos($items['sku'], '-F') !== false))) {

                            $ufcProduct = $this->UfcOrdersController->getUfcProduct($store, $items['product_id'], $items['sku']);
                            $i++;
                            $newSaleInvoice = new ListUfcOrders();
                            foreach ($items['properties'] as $item) {
                                if ((isset($item['name'])) && ($item['name'] == "Custom")) {
                                    $newSaleInvoice->setNom($item['value']);
                                    $newSaleInvoice->setNumCaractere(strlen($item['value']));

                                }
                                if ((isset($item['name'])) && (($item['name'] == "Country") || ($item['name'] == "Paese") || ($item['name'] == "Land")  || ($item['name'] == "País") )) {
                                    $newSaleInvoice->setPays($item['value']);
                                }
                                if ((isset($item['name'])) && (($item['name'] == "Preview") || ($item['name'] == "Voorbeeld") || ($item['name'] == "Anteprima")  || ($item['name'] == "Vista previa") || ($item['name'] == "Vorschau") )) {
                                    $newSaleInvoice->setImgBack($item['value']);
                                }
                                if ((isset($item['name'])) && (($item['name'] == "Preview1") || ($item['name'] == "Voorbeeld1")  || ($item['name'] == "Anteprima1") || ($item['name'] == "Vista previa1")  || ($item['name'] == "Vorschau1") )) {
                                    $newSaleInvoice->setImgFront($item['value']);
                                }
                                if ((isset($item['name'])) && ($item['name'] == "FIGHTER CHOICE")) {
                                    $newSaleInvoice->setNom($item['value']);
                                    $newSaleInvoice->setNumCaractere(strlen($item['value']));
                                }
                                if ((isset($item['name'])) && ($item['name'] == "Aperçu")) {
                                    $newSaleInvoice->setImgBack($item['value']);
                                }
                                if ((isset($item['name'])) && ($item['name'] == "Aperçu1")) {
                                    $newSaleInvoice->setImgFront($item['value']);
                                }

                            }

                            $newSaleInvoice->setSinceId($saleInvoice['id']);
                            $newSaleInvoice->setStore($storeId);
                            $newSaleInvoice->setNumCommande($saleInvoice['name']);
                            $newSaleInvoice->setStatus($saleInvoice['financial_status']);
                            $newSaleInvoice->setDateCommande(date('Y-m-d H:i:s', strtotime($saleInvoice['updated_at'])));
                            if ($saleInvoice['billing_address']) {
                                $newSaleInvoice->setPrenomClient($saleInvoice['billing_address']['first_name'] );
                                $newSaleInvoice->setNomClient($saleInvoice['billing_address']['last_name']);
                            } else {
                                $newSaleInvoice->setPrenomClient($saleInvoice['default_address']['first_name']);
                                $newSaleInvoice->setNomClient($saleInvoice['default_address']['last_name']);
                            }
                            $newSaleInvoice->setSku($items['sku']);
                            $adress= $saleInvoice['shipping_address']['first_name'].' '. $saleInvoice['shipping_address']['last_name']
                                .' <br> '.$saleInvoice['shipping_address']['address1']
                                .' <br> '.$saleInvoice['shipping_address']['city'].', '.$saleInvoice['shipping_address']['zip']
                                .' <br> '.$saleInvoice['shipping_address']['country']
                                .' <br>  Tel:  '.$saleInvoice['shipping_address']['phone'];
                            $newSaleInvoice->setAdress($adress);
                            $newSaleInvoice->setEnvoi($saleInvoice['shipping_lines']['0']['code']);
                            $newSaleInvoice->setTaille($ufcProduct['0']);
                            $skuParent= $this->container->get('doctrine')->getRepository(UfcProducts::class)->findOneBy(['sku' => str_replace('-'.$ufcProduct['0'],'',$items['sku'])]);
                                $newSaleInvoice->setCouleur($skuParent->getCouleur());
                                $newSaleInvoice->setFlocage($skuParent->getFlocage());

                            $newSaleInvoice->setType($ufcProduct['2']);

                            //$newSaleInvoice->setNumTracking();

                            $entityManager->persist($newSaleInvoice);
                            $entityManager->flush();

                        }
                    }

                }
            }
        }
        $lastInvoices = $this->container->get('doctrine')->getRepository(ListSinceId::class)->findOneBy(['storeId' => $storeId , 'list' => 'UfcOrders' ]);
        $entityManager = $this->doctrine->getManager();
        $lastInvoices->setSinceId($saleInvoice['id']);
        $lastInvoices->setDate(date('Y-m-d H:i:s', strtotime($saleInvoice['updated_at'])));
        $entityManager->flush();

        $count = $result['1'];
        if(!$count) {
            $output->writeln('no ufc order'. $store);
        } else {
            $output->writeln($i . ' / ' . $count . ' : new ufc orders '. $store);
        }
    }
}