<?php

namespace App\Command;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

use Symfony\Component\HttpClient\HttpClient;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Contracts\HttpClient\Exception\TransportExceptionInterface;
use Symfony\Component\HttpFoundation\Response;
use Psr\Container\ContainerInterface;
use Doctrine\Persistence\ManagerRegistry;
use App\Entity\Stores;
use App\Entity\ListSalesInvoices;
use App\Entity\ListSaleCreditMemo;
use App\Entity\DebInvoices;
use App\Controller\DebInvoicesController;

class DebInvoicesCommand extends Command
{
    protected static $defaultName = 'app:all-debs';
    protected static $defaultDescription = 'Get data to populate DEB Table';

    private $container;
    private $doctrine;
    private $debinvoicesController;

    public function __construct(
        ContainerInterface $container,
        DebInvoicesController $debinvoicesController,
        ManagerRegistry $doctrine
    ) {
        parent::__construct();
        $this->container = $container;
        $this->debinvoicesController = $debinvoicesController;
        $this->doctrine = $doctrine;
    }

    protected function configure(): void
    {
        $this
            ->setDescription(self::$defaultDescription)
            ->addArgument('store', InputArgument::REQUIRED, 'The store we are getting data for');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $output->writeln([
            '',
            'Debs Collector',
            '=================='
        ]);

        $io = new SymfonyStyle($input, $output);
        $store = $input->getArgument('store');

        $sites = array('ch', 'nl', 'it', 'es', 'en', 'fr', 'de'); // à rendre dynamique
        if ($store != 'all') {
            $sites = array($store);
        }
        $io->text('Processing ...');
        foreach ($sites as $site) {
            $storeId = $this->container->get('doctrine')->getRepository(Stores::class)->findOneByName($site)->getId();

            if (!$storeId) {
                continue;
            }
            $storeData = array('store' => $site, 'storeId' => $storeId);

            // Get Deb for invoices
            $io->text(' -> Collecting DEB Invoices for store ' . $storeData['store']);
            $this->processInvoices($storeData, $io);

            // Get Deb for Credits Notes
            $io->text(' -> Collecting DEB Credits Notes for store ' . $storeData['store']);
            $this->processCredits($storeData, $io);
        }

        $io->success('All operation ended !');

        return 0;
    }

    private function processInvoices($storeData, $io): void
    {
        $invoices = $this->container->get('doctrine')->getRepository(ListSalesInvoices::class)->findBy(['store' => $storeData['storeId'], 'isVisible' => 1, 'countryCode' => array('BE', 'IE')]);
        if ($invoices) {
            $io->progressStart();
            foreach ($invoices as $invoice) {
                $invoiceId = $invoice->getSinceId();
                $debOrderNumber = $this->container->get('doctrine')->getRepository(DebInvoices::class)->findOneBy(['order_id' => $invoiceId, 'store_id' => $storeData['storeId'], 'type_id' => 'invoice']);
                if ($invoiceId && !$debOrderNumber) {
                    $products = $this->debinvoicesController->getInvoicesData($storeData, $invoiceId);
                    if ($products) {
                        foreach ($products  as $product) {
                            $product['num_facture'] = $invoice->getNumFacture();
                            $product['date_facture'] = $invoice->getDateFacture();
                            $this->saveDebs($product);
                        }
                    }
                    $io->progressAdvance();
                }
            }
            $io->progressFinish();
        }
    }

    private function processCredits($storeData, $io): void
    {
        $credits = $this->container->get('doctrine')->getRepository(ListSaleCreditMemo::class)->findBy(['store' => $storeData['storeId'], 'isVisible' => 1, 'countryCode' => array('BE', 'IE')]);
        if ($credits) {
            $io->progressStart();
            foreach ($credits as $credit) {
                $creditId = $credit->getRefundId();
                $orderId = $credit->getSinceId();
                $debOrderNumber = $this->container->get('doctrine')->getRepository(DebInvoices::class)->findOneBy(['order_id' => $creditId, 'store_id' => $storeData['storeId'], 'type_id' => 'credit']);
                if ($creditId && !$debOrderNumber) {
                    $products = $this->debinvoicesController->getCreditsData($storeData, $creditId, $orderId);
                    if ($products) {
                        foreach ($products  as $product) {
                            $product['num_facture'] = $credit->getNumCreditmemo();
                            $product['date_facture'] = $credit->getDateCreditmemo();
                            $this->saveDebs($product);
                        }
                    }
                    $io->progressAdvance();
                }
            }
            $io->progressFinish();
        }
    }

    private function saveDebs($datas): bool
    {
        if (isset($datas) && !empty($datas)) {
            $newDebs = new DebInvoices();
            foreach ($datas as $property => $value) {
                $newDebs->setData($property, $value);
            }
            try {
                $this->container->get('doctrine')->getRepository(DebInvoices::class)->add($newDebs);
            } catch (\Exception $ex) {
                echo "Exception Found - " . $ex->getMessage() . "\n";
            }
            return true;
        }
        return false;
    }
}
