<?php

namespace App\Command;

use App\Controller\CaNewSkuController;
use App\Entity\CaNewSku;
use App\Repository\CaNewSkuRepository;
use App\Repository\StoresRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

class CaNewSkuCommand extends Command
{
    protected static $defaultName = 'app:all-ca-new-sku';
    protected static $defaultDescription = 'Add a short description for your command';

    private StoresRepository $storeRepository;
    private CaNewSkuRepository $repository;
    private EntityManagerInterface $entityManager;
    private CaNewSkuController $controller;

    public function __construct(StoresRepository $storeRepository, CaNewSkuRepository $repository, EntityManagerInterface $entityManager, CaNewSkuController $controller)
    {
        parent::__construct();
        $this->storeRepository = $storeRepository;
        $this->repository = $repository;
        $this->entityManager = $entityManager;
        $this->controller = $controller;
    }

    protected function configure(): void
    {
        $this->addArgument('store', InputArgument::REQUIRED, 'store')
            ->addArgument('sinceid', InputArgument::OPTIONAL, 'since_id');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $output->writeln([
            '',
            'Ca New SKU  Collector',
            '=================='
        ]);

        $io = new SymfonyStyle($input, $output);
        $store = $input->getArgument('store');
        $sinceId = $input->getArgument('sinceid');






        $storeId = $this->storeRepository->findOneByName($store)->getId();

        if ($sinceId != null) {
            $lastInvoice = 0;
        } else {
            $lastInvoice =  $this->repository->findOneBy(['store' => $storeId], array('id' => 'desc'), 1, 0);
            if ($lastInvoice) {
                $lastInvoice = $lastInvoice->getsinceId();
            } else {
                $lastInvoice = 0;
            }
        }

        $storeData = array('store' => $store, 'storeId' => $storeId);

        // Get Deb for invoices
        $io->text(' -> Collecting Ca New Sku for store ' . $storeData['store']);

        $this->processOperation($storeData, $io,  $lastInvoice);

        $io->success('All operation ended !');
        return 0;
    }

    private function processOperation($storeData, $io,  $sinceId): void
    {

        $io->progressStart();
        $products = $this->controller->getCaNewSku($storeData, $sinceId);
        if ($products) {
            foreach ($products as  $value) {
                foreach ($value  as $product) {
                    $this->saveDebs($product);
                }
            }
        }
        $io->progressAdvance();

        $io->progressFinish();
    }

    private function saveDebs($datas): bool
    {
        if (isset($datas) && !empty($datas)) {
            $caNewSku = new CaNewSku();
            foreach ($datas as  $property => $value) {
                $caNewSku->setData($property, $value);
            }
            try {
                $this->repository->add($caNewSku);
            } catch (\Exception $ex) {
                echo "Exception Found - " . $ex->getMessage() . "\n";
            }
            return true;
        }
        return false;
    }
}
