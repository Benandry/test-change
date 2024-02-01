<?php

namespace App\Command;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\Finder\Finder;
use Symfony\Component\HttpClient\HttpClient;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Contracts\HttpClient\Exception\TransportExceptionInterface;
use Symfony\Component\HttpFoundation\Response;
use Psr\Container\ContainerInterface;
use Doctrine\Persistence\ManagerRegistry;
use SecIT\ImapBundle\Service\Imap;
use App\Entity\ListSalesInvoices;
use App\Entity\ListTvaInvoices;
use App\Entity\Tvaoss;
use App\Entity\ListTvaCreditmemo;
use App\Entity\ListSaleCreditMemo;
use App\Entity\CaSku;
use App\Entity\CaFacture;
use App\Entity\CaPays;
use App\Entity\CaComptable;
use App\Entity\CaNewSku;
use App\Entity\CmFacture;
use App\Entity\CmSku;


class SufioInvoicesCommand extends Command
{
	private $container;
	private $doctrine;

	protected static $defaultName = 'app:sufio-invoices';
	protected static $defaultDescription = 'Command to retrieve Sufio Invoices';

	public function __construct(
		ContainerInterface $container,
		ManagerRegistry $doctrine
	) {
		parent::__construct();
		$this->container = $container;
		$this->doctrine = $doctrine;
	}

	protected function configure(): void
	{
		$this
			->setDescription(self::$defaultDescription)
			->addArgument('store', InputArgument::OPTIONAL, 'The store we are getting invoices for');
	}

	protected function execute(InputInterface $input, OutputInterface $output): int
	{

		$io = new SymfonyStyle($input, $output);
		$io->newLine();
		$output->writeln([
			'Invoices Collector',
			'==================',
			'',
		]);

		$store = $input->getArgument('store');

		if ($store) {
			$io->note(sprintf('You passed an argument: %s', $store));
		}

		$datas = $this->parseCsv();
		if ($datas && !empty($datas)) {

			$io->text('Updating TvaInvoices and Invoices ...');
			$io->newLine();
			$io->progressStart();
			if ($datas['type'] == 'invoices') {
				$datas = $datas['data'];
				foreach ($datas as $data) {
					$this->updateTvaInvoice($data);
					$this->updateSalesInvoice($data);
					$this->updateCaFacture($data);
					$this->updateCaSku($data);
					$this->updateCaNewSku($data);
					$this->updateCaPays($data);
					$this->updateCaComptable($data);
					$io->progressAdvance();
				}
			} else {
				$datas = $datas['data'];

				foreach ($datas as $data) {

					$this->updateSalesCredits($data);
					$this->updateTvaCredits($data);
					$this->updateCmFactureCredits($data);
					$this->updateCmSkuCredits($data);
					$io->progressAdvance();
				}
			}

			$io->progressFinish();
			$io->success('DONE !');
		} else {
			echo 'No file found!' . "\n";
			$io->newLine();
		}

		return 0;
	}

	private function parseCsv()
	{
		$finder = new Finder();
		$finder->exclude('tmp')->exclude('archives');
		$header = array(
			0 => 'invoice_number', 1 => 'issue_date', 5 => 'subtotal', 6 => 'total_excl_tax', 7 => 'taxes_total', 8 => 'invoice_total', 12 => 'tax_rate', 13 => 'tax_amount',
			26 => 'currency', 33 => 'country_code', 40 => 'payment_method', 42 => 'order_number', 43 => 'discount_percent', 44 => 'discount_amount', 45 => 'shipping'
		);
		$csvData = array();
		$i = 0;
		foreach ($finder->in('var/sufio') as $file) {
			if (preg_match('/Invoices|Credit/', $file)) {
				echo $file . "\n";
				if (($handle = fopen($file, "r")) !== FALSE) {
					fgetcsv($handle);
					while (($datas = fgetcsv($handle, 1000, ",")) !== FALSE) {
						$csvLines = array();
						foreach ($datas as $key => $data) {
							if (isset($header[$key]) && isset($data)) {
								$csvLines[$header[$key]] = $data;
							}
						}
						$csvData[] = $csvLines;
						$i++;
						unset($csvLines);
					}
					fclose($handle);
					rename($file, 'var/sufio/archives/' . basename($file));
				}
				if (preg_match('/Invoices/', $file)) {
					return $i > 0 ? array('type' => 'invoices', 'data' => $csvData) : null;
				} else {
					return $i > 0 ? array('type' => 'credits', 'data' => $csvData) : null;
				}
			}
		}
	}

	private function readSufioFile($file)
	{
	}

	private function updateTvaInvoice($data)
	{

		$tvaInvoice = $this->container->get('doctrine')->getRepository(ListTvaInvoices::class)->findOneBy(['numCommande' => $data['order_number']]);
		if ($tvaInvoice) {
			$entityManager = $this->doctrine->getManager();
			$tvaInvoice->setNumFacture($data['invoice_number']);
			$tvaInvoice->setDateFacture($data['issue_date'] . ' 19:00:00');
			if ($data['country_code'] == 'NL' || $data['country_code'] == 'BE') {
				$tvaInvoice->setBaseHt21(abs($data['total_excl_tax']));
				$tvaInvoice->setTva21(abs($data['taxes_total']));
			} elseif ($data['country_code'] == 'FR') {
				$tvaInvoice->setBaseHt20(abs($data['total_excl_tax']));
				$tvaInvoice->setTva20(abs($data['taxes_total']));
			} else {
				$tvaInvoice->setBaseHtMulti(abs($data['total_excl_tax']));
				$tvaInvoice->setTvaMulti(abs($data['taxes_total']));
				$tvaInvoice->setBaseHt21(0);
				$tvaInvoice->setTva21(0);
				$tvaInvoice->setBaseHt20(0);
				$tvaInvoice->setTva20(0);
			}
			$tvaInvoice->setTauxTaxe(str_replace('%', '', $data['tax_rate']));
			$tvaInvoice->setDiscount(abs($data['discount_amount']));
			$tvaInvoice->setSubtotal(abs($data['total_excl_tax']));
			$tvaInvoice->setTotalTtc(abs($data['subtotal']));
			$tvaInvoice->setTaxeLivraison(abs($data['TaxeLivraison']));
			$tvaInvoice->setShipping(abs($data['shipping']));
			$tvaInvoice->setMontantTaxe(abs($data['tax_amount']));
			$tvaInvoice->setDiscountPercent($data['discount_percent']);

			$tvaInvoice->setIsVisible(1);
			$tvaInvoice->setEcart(0);
			$entityManager->flush();
		} else {
			echo 'updateTvaInvoice order missing : ' . $data['order_number'] . "\n";
		}
	}

	private function updateSalesInvoice($data)
	{

		$salesInvoice = $this->container->get('doctrine')->getRepository(ListSalesInvoices::class)->findOneBy(['numCommande' => $data['order_number']]);
		if ($salesInvoice) {
			$entityManager = $this->doctrine->getManager();
			$salesInvoice->setNumFacture($data['invoice_number']);
			$salesInvoice->setDateFacture($data['issue_date'] . ' 19:00:00');
			$salesInvoice->setSubtotal($data['total_excl_tax']);
			$salesInvoice->setIsVisible(1);
			$entityManager->flush();
		} else {
			echo 'updateSalesInvoice order missing : ' . $data['order_number'] . "\n";
		}
	}

	private function updateCaSku($data)
	{

		$salesInvoice = $this->container->get('doctrine')->getRepository(CaSku::class)->findBy(['numCommande' => $data['order_number']]);
		if ($salesInvoice) {
			foreach ($salesInvoice as $invoices) {
				$entityManager = $this->doctrine->getManager();
				$invoices->setNumFacture($data['invoice_number']);
				$invoices->setDateFacture($data['issue_date'] . ' 19:00:00');
				$invoices->setIsVisible(1);
				$entityManager->flush();
			}
		} else {
			echo 'updateNewCaSku : order missing : ' . $data['order_number'] . "\n";
		}
	}


	private function updateCaNewSku($data)
	{
		$salesInvoice = $this->container->get('doctrine')->getRepository(CaNewSku::class)->findBy(['numCommande' => $data['order_number']]);
		if ($salesInvoice) {
			foreach ($salesInvoice as $invoices) {
				$entityManager = $this->doctrine->getManager();
				$invoices->setNumFacture($data['invoice_number']);
				$invoices->setDateFacture($data['issue_date'] . ' 19:00:00');
				$invoices->setIsVisible(1);
				$entityManager->flush();
			}
		} else {
			//echo 'updateCaSku : order missing : ' . $data['order_number'] . "\n";
		}
	}


	private function updateCaFacture($data)
	{

		$salesInvoice = $this->container->get('doctrine')->getRepository(CaFacture::class)->findOneBy(['numCommande' => $data['order_number']]);
		if ($salesInvoice) {
			$entityManager = $this->doctrine->getManager();
			$salesInvoice->setNumFacture($data['invoice_number']);
			$salesInvoice->setDateFacture($data['issue_date'] . ' 19:00:00');
			$salesInvoice->setIsVisible(1);
			$entityManager->flush();
		} else {
			//echo 'updateCaFacture order missing : ' . $data['order_number'] . "\n";
		}
	}

	private function updateCaComptable($data)
	{

		$salesInvoice = $this->container->get('doctrine')->getRepository(CaComptable::class)->findOneBy(['numCommande' => $data['order_number']]);
		if ($salesInvoice) {
			$entityManager = $this->doctrine->getManager();
			$salesInvoice->setNumFacture($data['invoice_number']);
			$salesInvoice->setDateFacture($data['issue_date'] . ' 19:00:00');
			$salesInvoice->setIsVisible(1);
			$entityManager->flush();
		} else {
			//echo 'updateCaComptable order missing : ' . $data['order_number'] . "\n";
		}
	}

	private function updateCaPays($data)
	{

		$salesInvoice = $this->container->get('doctrine')->getRepository(CaPays::class)->findOneBy(['numCommande' => $data['order_number']]);
		if ($salesInvoice) {
			$entityManager = $this->doctrine->getManager();
			$salesInvoice->setNumFacture($data['invoice_number']);
			$salesInvoice->setDateFacture($data['issue_date'] . ' 19:00:00');
			$salesInvoice->setIsVisible(1);
			$entityManager->flush();
		} else {
			//echo 'updateCaPays order missing : ' . $data['order_number'] . "\n";
		}
	}

	private function updateTvaCredits($data)
	{

		$tvaCredit = null;
		$tvaCredits = $this->container->get('doctrine')->getRepository(ListTvaCreditmemo::class)->findBy(['numCommande' => $data['order_number']]);

		if (count($tvaCredits) > 0) {
			foreach ($tvaCredits as $tvaCreditToFind) {
				$creditDate = explode(' ', $tvaCreditToFind->getDateFacture());
				if ($creditDate[0] == $data['issue_date']) {
					$tvaCredit = $tvaCreditToFind;
					break;
				} else {
					if (count($tvaCredits) > 1 && $tvaCreditToFind->getIsVisible() == 0) {
						if ($tvaCreditToFind->getTotalTtc() == abs($data['subtotal'])) {
							$tvaCredit = $tvaCreditToFind;
						}
					} else {
						$tvaCredit = $tvaCreditToFind;
					}
				}
			}
		} else {
			$tvaCredit = $tvaCredits[0];
		}
		if ($tvaCredit) {
			$entityManager = $this->doctrine->getManager();
			$tvaCredit->setNumFacture($data['invoice_number']);
			$tvaCredit->setDateFacture($data['issue_date'] . ' 19:00:00');

			if ($data['country_code'] == 'NL' || $data['country_code'] == 'BE') {
				$tvaCredit->setBaseHt21(abs($data['total_excl_tax']));
				$tvaCredit->setTva21(abs($data['taxes_total']));
			} elseif ($data['country_code'] == 'FR') {
				$tvaCredit->setBaseHt20(abs($data['total_excl_tax']));
				$tvaCredit->setTva20(abs($data['taxes_total']));
			} else {
				$tvaCredit->setBaseHtMulti(abs($data['total_excl_tax']));
				$tvaCredit->setTvaMulti(abs($data['taxes_total']));
				$tvaCredit->setBaseHt21(0);
				$tvaCredit->setTva21(0);
				$tvaCredit->setBaseHt20(0);
				$tvaCredit->setTva20(0);
			}
			$tvaCredit->setTauxTaxe(str_replace('%', '', $data['tax_rate']));
			$tvaCredit->setDiscount(abs($data['discount_amount']));
			$tvaCredit->setSubtotal(abs($data['total_excl_tax']));
			$tvaCredit->setTotalTtc(abs($data['subtotal']));
			$tvaCredit->setTaxeLivraison(abs($data['TaxeLivraison']));
			$tvaCredit->setShipping(abs($data['shipping']));
			$tvaCredit->setMontantTaxe(abs($data['tax_amount']));
			$tvaCredit->setDiscountPercent($data['discount_percent']);
			$tvaCredit->setEcart(0);
			$tvaCredit->setIsVisible(1);
			$entityManager->flush();
		} else {
			echo 'updateTvaCredits order missing : ' . $data['order_number'] . "\n";
		}
	}

	private function updateSalesCredits($data)
	{
		$salesCredit = null;
		$salesCredits = $this->container->get('doctrine')->getRepository(ListSaleCreditMemo::class)->findBy(['numCommande' => $data['order_number']]);

		if (count($salesCredits) > 0) {
			foreach ($salesCredits as $salesCreditToFind) {
				$creditDate = explode(' ', $salesCreditToFind->getDateCreditmemo());
				if ($creditDate[0] == $data['issue_date']) {
					$salesCredit = $salesCreditToFind;
				} else {
					if (count($salesCredits) > 1 && $salesCreditToFind->getIsVisible() == 0) {
						if ($salesCreditToFind->getSubtotal() == abs($data['total_excl_tax'])) {
							$salesCredit = $salesCreditToFind;
						}
					} else {
						$salesCredit = $salesCreditToFind;
					}
				}

				if ($salesCredit) {
					$entityManager = $this->doctrine->getManager();
					$salesCredit->setNumCreditmemo($data['invoice_number']);
					$salesCredit->setDateCreditmemo($data['issue_date'] . ' 19:00:00');
					$salesCredit->setSubtotal(abs($data['total_excl_tax']));
					$salesCredit->setIsVisible(1);
					$entityManager->flush();
				}
			}
		} else {
			echo 'updateSalesCredits order missing : ' . $data['order_number'] . "\n";
		}
	}

	private function updateCmFactureCredits($data)
	{
		$salesCredit = $this->container->get('doctrine')->getRepository(CmFacture::class)->findOneBy(['numCreditmemo' => $data['order_number']]);
		if ($salesCredit) {
			$entityManager = $this->doctrine->getManager();
			$salesCredit->setNumCreditmemo($data['invoice_number']);
			$salesCredit->setDateCreditmemo($data['issue_date'] . ' 19:00:00');
			$salesCredit->setSubtotal(abs($data['total_excl_tax']));
			$entityManager->flush();
		}
	}

	private function updateCmSkuCredits($data)
	{
		$salesCredit = $this->container->get('doctrine')->getRepository(CmSku::class)->findOneBy(['numCreditmemo' => $data['order_number']]);
		if ($salesCredit) {
			$entityManager = $this->doctrine->getManager();
			$salesCredit->setNumCreditmemo($data['invoice_number']);
			$salesCredit->setDateCreditmemo($data['issue_date'] . ' 19:00:00');
			$entityManager->flush();
		}
	}

	private function updateTvaOss($data)
	{
		$tvaoss = $this->container->get('doctrine')->getRepository(Tvaoss::class)->findBy(['num_facture' => $data['order_number'], 'type_id' => 'facture_produits']);
		if ($tvaoss && !empty($tvaoss)) {
			foreach ($tvaoss as $oss) {
				$entityManager = $this->doctrine->getManager();
				$oss->setNumFacture($data['invoice_number']);
				$oss->setDateFacture($data['issue_date'] . ' 19:00:00');
				$oss->setDatePaiement($data['issue_date'] . ' 19:00:00');
				$oss->setDateLivraison(date('Y-m-d H:i:s', strtotime($data['issue_date'] . " +2 days")));
				$entityManager->flush();
			}
		}

		$tvaossShippings = $this->container->get('doctrine')->getRepository(Tvaoss::class)->findBy(['num_facture' => $data['order_number'], 'type_id' => 'livraison_produits']);
		if ($tvaossShippings && !empty($tvaossShippings)) {
			foreach ($tvaossShippings as $tvaossShipping) {
				$entityManager = $this->doctrine->getManager();
				$tvaossShipping->setNumFacture($data['invoice_number']);
				$tvaossShipping->setDateFacture($data['issue_date'] . ' 19:00:00');
				$tvaossShipping->setDatePaiement($data['issue_date'] . ' 19:00:00');
				$tvaossShipping->setDateLivraison(date('Y-m-d H:i:s', strtotime($data['issue_date'] . " +2 days")));
				$entityManager->flush();
			}
		}
	}

	private function updateTvaOssCredit($data)
	{

		// avoir produits
		$exOrders = array('#VNM-1014-NL', '#VNM-1233-NL', '#VNM-1355-NL', '#VNM-1144-NL');
		$tvaossCredits = $this->container->get('doctrine')->getRepository(Tvaoss::class)->findBy(['num_facture' => $data['order_number'], 'type_id' => 'avoir_produits']);
		if ($tvaossCredits && !empty($tvaossCredits)) {
			foreach ($tvaossCredits as $ossCredit) {
				$creditDate = explode(' ', $ossCredit->getDateFacture());
				if (($creditDate[0] == $data['issue_date']) || in_array($data['order_number'], $exOrders)) {
					$entityManager = $this->doctrine->getManager();
					$ossCredit->setNumFacture($data['invoice_number']);
					$ossCredit->setDateFacture($data['issue_date'] . ' 19:00:00');
					$ossCredit->setDatePaiement($data['issue_date'] . ' 19:00:00');
					$ossCredit->setDateLivraison(date('Y-m-d H:i:s', strtotime($data['issue_date'] . " +2 days")));
					$entityManager->flush();
				} else {
					//echo 'updateTvaOssCredit : ' . $data['order_number'] . ' : ' . $creditDate[0] . ' - ' . $data['issue_date'] . "\n" ;
				}
			}
		}

		//avoir livraisons
		$tvaossShippings = $this->container->get('doctrine')->getRepository(Tvaoss::class)->findBy(['num_facture' => $data['order_number'], 'type_id' => 'avoir_livraisons']);
		if ($tvaossShippings && !empty($tvaossShippings)) {
			foreach ($tvaossShippings as $tvaossShipping) {
				$creditDate = explode(' ', $tvaossShipping->getDateFacture());
				if (($creditDate[0] == $data['issue_date']) || in_array($data['order_number'], $exOrders)) {
					$entityManager = $this->doctrine->getManager();
					$tvaossShipping->setNumFacture($data['invoice_number']);
					$tvaossShipping->setDateFacture($data['issue_date'] . ' 19:00:00');
					$tvaossShipping->setDatePaiement($data['issue_date'] . ' 19:00:00');
					$tvaossShipping->setDateLivraison(date('Y-m-d H:i:s', strtotime($data['issue_date'] . " +2 days")));
					$entityManager->flush();
				}
			}
		}
	}
}
