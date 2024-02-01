<?php

namespace App\Command;

use Doctrine\Persistence\ManagerRegistry;
use Psr\Log\NullLogger;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Psr\Container\ContainerInterface;
use App\Controller\CmSkuController;
use Symfony\Component\HttpClient\HttpClient;
use App\Entity\CmSku;
use App\Entity\Stores;
use Symfony\Component\Console\Input\InputArgument;

class CmSkuCommand extends Command
{
    // the name of the command (the part after "bin/console")
    protected static $defaultName = 'app:all-cm-sku';

    private $container;
    private $CmSkuController;

    private $doctrine;

    public function __construct(
        ContainerInterface $container,
        CmSkuController $CmSkuController,
        ManagerRegistry $doctrine)
    {
        parent::__construct();
        $this->container = $container;
        $this->CmSkuController = $CmSkuController;
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
		if($sinceId!=null) {
			$lastInvoice = 0;
		}else {
			$lastInvoice=  $this->container->get('doctrine')->getRepository(CmSku::class)->findOneBy(['store' => $storeId], array('id' => 'desc'),1,0);
			if($lastInvoice) {
				$lastInvoice=$lastInvoice->getsinceId();
			}
			else {
				$lastInvoice=0;

			}
		}
        $entityManager = $this->doctrine->getManager();
        $result = $this->CmSkuController->getAllInvoices($store, $lastInvoice);
        $allInvoices = $result['0'];
        $i=0;
       if ($allInvoices) {
            foreach ($allInvoices as $saleInvoice) {
                $invoice = $this->container->get('doctrine')->getRepository(CmSku::class)->findOneBy(['sinceId' => $saleInvoice['id']]);
                if (!$invoice) {

                    foreach ($saleInvoice['refunds'] as $items) {
                        foreach ($items['refund_line_items'] as $item) {
                            $i++;
                            $newSaleInvoice = new CmSku();
                            $newSaleInvoice->setSinceId($saleInvoice['id']);
                            $newSaleInvoice->setNumCommande($saleInvoice['name']);
                            $newSaleInvoice->setNumCreditmemo($saleInvoice['name']);
                            $newSaleInvoice->setStore($storeId);
                            $newSaleInvoice->setDateCreditmemo($saleInvoice['created_at']);
                            foreach ($saleInvoice['tax_lines'] as $tax) {
                                $tax = $tax['rate'];
                            }
                            if ($item['line_item']['product_id']) {
                                $newSaleInvoice->setProductId($item['line_item']['product_id']);
                            }
                            $newSaleInvoice->setSku($item['line_item']['sku']);
                            $newSaleInvoice->setName($item['line_item']['name']);
                            $newSaleInvoice->setQty($item['line_item']['quantity']);
                            $subTotal = $item['line_item']['price'] / (1 + $tax);
                            $newSaleInvoice->setRowTotalTax($subTotal * ($tax + 1));
                            $newSaleInvoice->setTax($saleInvoice['current_total_tax']);
                            $entityManager->persist($newSaleInvoice);
                            $entityManager->flush();
                        }
                    }
                }else{
					echo 'Order ' . $saleInvoice['name'] . ' already in database. Shipped !' . "\n";
				}
            }
        }

        $count = $result['1'];
        if(!$count) {
            $output->writeln('no credit memo '. $store);
        } else {
            $output->writeln($i . ' / ' . $count . ' : new credit memo '. $store);
        }
    }
}