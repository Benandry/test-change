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
use App\Entity\Tvaoss;
use App\Controller\TvaossController;
use DateTime;
use DateTimeZone;

class TvaossCommand extends Command
{
    protected static $defaultName = 'app:all-tvaoss';
    protected static $defaultDescription = 'Get data to populate TVA Oss Table';

    private $container;
	private $doctrine;
    private $tvaossController;

    public function __construct(
        ContainerInterface $container,
        TvaossController $tvaossController,
        ManagerRegistry $doctrine)
    {
        parent::__construct();
        $this->container = $container;
        $this->tvaossController = $tvaossController;
        $this->doctrine = $doctrine;
    }

    protected function configure(): void
    {
        $this
            ->setDescription(self::$defaultDescription)
            ->addArgument('store', InputArgument::REQUIRED, 'The store we are getting data for')
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $output->writeln([
			'TvaOss Collector',
			'==================',
			'',
		]);

        $io = new SymfonyStyle($input, $output);
        $store = $input->getArgument('store');
        
        $sites = explode(',','nl,it,es,en,de'); // à rendre dynamique
        if($store!='all'){
            $sites = array($store);
        }
        
        foreach($sites as $site){

            $storeId = $this->container->get('doctrine')->getRepository(Stores::class)->findOneByName($site)->getId();
            if(!$storeId){
                continue;
            }
            $storeData = array('store'=>$site,'storeId'=>$storeId);

            // Get Oss for Shipping invoiced
            $io->text('Collecting Livraisons ...');
            $this->getLivraisons($storeData, $io);
            $io->text('Livraisons done !');

            // Get Oss for invoices
            $io->text('Collecting InvoicesSso ...');
            $this->getInvoicesSso($storeData, $io);
            $io->text('InvoicesSso done !');

            // Get Oss for Credit Memos
            $io->text('Collecting CreditsSso ...');
            $this->getCreditsSso($storeData, $io);
            $io->text('CreditsSso done !');

            // Get Oss for Shipping Refunded
            $io->text('Collecting CreditLivraisons ...');
            $this->getCreditLivraisons($storeData, $io);
            $io->text('CreditLivraisons done !');
        }

        $io->success('All operation ended !');

        return 0;
    }

    private function getLivraisons($storeData, $io): void
    {
        $datetime = new DateTime('now', new DateTimeZone('Europe/Paris'));
        $datetime->modify("-40 days");
        $recentDate = $datetime->format('Y-m-d');
        $livraisons = $this->container->get('doctrine')->getRepository(ListSalesInvoices::class)->findBySalesInvoicesObjet($recentDate, $storeData['storeId']);
        if($livraisons){
            $io->progressStart();
            foreach($livraisons as $livraison){
                $livraisonId = $livraison->getSinceId();
                $tvaOssOrderNumber = $this->container->get('doctrine')->getRepository(Tvaoss::class)->findOneBy(['order_id' => $livraisonId, 'type_id'=>'livraison_produits']);
                if($livraisonId && !$tvaOssOrderNumber) {
                    $products = $this->tvaossController->getShippings($storeData['store'], $livraisonId);
                    if($products){
                        $this->saveTvaOss($products, $storeData['storeId'], $livraison->getNumFacture(), $livraison->getDateFacture());
                        $io->progressAdvance();
                    }
                }
            }
            $io->progressFinish();
        }
    }

    private function getCreditLivraisons($storeData, $io): void
    {
        $credits = $this->container->get('doctrine')->getRepository(ListSaleCreditMemo::class)->findBy(['store' => $storeData['storeId'], 'isVisible'=> 1]);
        if($credits){
            $io->progressStart();
            foreach($credits as $credit){
                $creditId = $credit->getRefundId();
                $orderId = $credit->getSinceId();
                $tvaOssOrderNumber = $this->container->get('doctrine')->getRepository(Tvaoss::class)->findOneBy(['order_id' => $creditId, 'type_id'=>'avoir_livraisons']);
                
                if($creditId && !$tvaOssOrderNumber) {
                    $refundedShipping = $this->tvaossController->getRefundedShipping($storeData['store'], $orderId, $creditId);
                    if($refundedShipping){
                        $this->saveTvaOss($refundedShipping, $storeData['storeId'], $credit->getNumCreditmemo(), $credit->getDateCreditmemo());
                        $io->progressAdvance();
                    }
                }
            }
            $io->progressFinish();
        }
    }

    private function getCreditsSso($storeData, $io): void
    {
        $credits = $this->container->get('doctrine')->getRepository(ListSaleCreditMemo::class)->findBy(['store' => $storeData['storeId'], 'isVisible'=> 1]);
        if($credits){
            $io->progressStart();
            foreach($credits as $credit){
                $creditId = $credit->getRefundId();
                
                $orderId = $credit->getSinceId();
                $tvaOssOrderNumber = $this->container->get('doctrine')->getRepository(Tvaoss::class)->findOneBy(['order_id' => $creditId, 'type_id'=>'avoir_produits']);
                if($creditId && !$tvaOssOrderNumber) {
                    $refundedProducts = $this->tvaossController->getRefundedProducts($storeData['store'], $orderId, $creditId);
                    if($refundedProducts){
                        $this->saveTvaOss($refundedProducts, $storeData['storeId'],  $credit->getNumCreditmemo(), $credit->getDateCreditmemo());
                        $io->progressAdvance();
                    }
                }
            }
            $io->progressFinish();
        }
    }

    private function getInvoicesSso($storeData, $io): void
    {
        $datetime = new DateTime('now', new DateTimeZone('Europe/Paris'));
        $datetime->modify("-40 days");
        $recentDate = $datetime->format('Y-m-d');
        $invoices = $this->container->get('doctrine')->getRepository(ListSalesInvoices::class)->findBySalesInvoicesObjet($recentDate, $storeData['storeId']);
        if($invoices){
            $io->progressStart();
            foreach($invoices as $invoice){
                $invoiceId = $invoice->getSinceId();
                $tvaOssOrderNumber = $this->container->get('doctrine')->getRepository(Tvaoss::class)->findOneBy(['order_id' => $invoiceId, 'type_id'=>'facture_produits']);
                if($invoiceId && !$tvaOssOrderNumber) {
                    $products = $this->tvaossController->getOrderProducts($storeData['store'], $invoiceId);
                    if($products){
                        $this->saveTvaOss($products, $storeData['storeId'], $invoice->getNumFacture(), $invoice->getDateFacture());
                        $io->progressAdvance();
                    }
                }
            }
            $io->progressFinish();
        }
    }

    private function saveTvaOss($datas, $storeId, $num, $date): bool
    {
        if(isset($datas) && !empty($datas)){
            foreach($datas as $data) {
                $newTvaOss = new Tvaoss();
                foreach($data as $property => $value) {
                    $newTvaOss->setData($property, $value);
                }
                $newTvaOss->setData('store', $storeId);
                $newTvaOss->setNumFacture($num);
				$newTvaOss->setDateFacture($date);
				$newTvaOss->setDatePaiement($date);
				$newTvaOss->setDateLivraison(date('Y-m-d H:i:s', strtotime($data['issue_date'] . " +2 days")));
                try{
                    $this->container->get('doctrine')->getRepository(Tvaoss::class)->add($newTvaOss);
                }catch(\Exception $ex){
                    echo "Exception Found - " . $ex->getMessage() . "\n";
                }
            }
            return true;
        }
        return false;
    }
}
