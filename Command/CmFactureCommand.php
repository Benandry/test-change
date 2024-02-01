<?php

namespace App\Command;

use Doctrine\Persistence\ManagerRegistry;
use Psr\Log\NullLogger;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Psr\Container\ContainerInterface;
use App\Controller\CmFactureController;
use Symfony\Component\HttpClient\HttpClient;
use App\Entity\CmFacture;
use App\Entity\Stores;
use Symfony\Component\Console\Input\InputArgument;

class CmFactureCommand extends Command
{
    // the name of the command (the part after "bin/console")
    protected static $defaultName = 'app:all-cm-facture';

    private $container;
    private $CmFactureController;

    private $doctrine;

    public function __construct(
        ContainerInterface $container,
        CmFactureController $CmFactureController,
        ManagerRegistry $doctrine
    ) {
        parent::__construct();
        $this->container = $container;
        $this->CmFactureController = $CmFactureController;
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
            $lastInvoice =  $this->container->get('doctrine')->getRepository(CmFacture::class)->findOneBy(['store' => $storeId], array('id' => 'desc'), 1, 0);
            if ($lastInvoice) {
                $sinceId = $lastInvoice->getsinceId();
            } else {
                // $lastInvoice=0;
                $sinceId = 0;
            }
        }
        $entityManager = $this->doctrine->getManager();
        $result = $this->CmFactureController->getAllInvoices($store, $sinceId);
        $allInvoices = $result['0'];
        $i = 0;
        if ($allInvoices) {
            foreach ($allInvoices as $saleInvoice) {
                $invoice = $this->container->get('doctrine')->getRepository(CmFacture::class)->findOneBy(['sinceId' => $saleInvoice['id']]);
                if (!$invoice) {
                    foreach ($saleInvoice['refunds'] as $items) {
                        foreach ($items['transactions'] as $item) {
                            $i++;
                            $newSaleInvoice = new CmFacture();
                            $newSaleInvoice->setSinceId($saleInvoice['id']);
                            $newSaleInvoice->setNumCreditmemo($saleInvoice['name']);
                            $newSaleInvoice->setStore($storeId);
                            $newSaleInvoice->setDateCreditmemo($saleInvoice['created_at']);
                            if (isset($saleInvoice['billing_address'])) {
                                $newSaleInvoice->setBillingClient($saleInvoice['billing_address']['first_name'] . " " . $saleInvoice['billing_address']['last_name']);
                                $newSaleInvoice->setNomClient($saleInvoice['billing_address']['first_name'] . " " . $saleInvoice['billing_address']['last_name']);
                            }
                            if (isset($saleInvoice['default_address'])) {
                                $newSaleInvoice->setNomClient($saleInvoice['default_address']['first_name'] . " " . $saleInvoice['default_address']['last_name']);
                            }
                            $newSaleInvoice->setEmailClient($saleInvoice['email']);

                            $newSaleInvoice->setSubtotal($item['amount']);
                            $newSaleInvoice->setShipping("0.00");
                            $entityManager->persist($newSaleInvoice);
                            $entityManager->flush();
                        }
                    }
                } else {
                    echo 'Order ' . $saleInvoice['name'] . ' already in database. Shipped !' . "\n";
                }
            }
        }

        $count = $result['1'];
        if (!$count) {
            $output->writeln('no credit memo ' . $store);
        } else {
            $output->writeln($i . ' / ' . $count . ' : new credit memo ' . $store);
        }
    }
}
