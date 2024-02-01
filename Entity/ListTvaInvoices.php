<?php

namespace App\Entity;

use App\Repository\ListTvaInvoicesRepository;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass=ListTvaInvoicesRepository::class)
 */
class ListTvaInvoices
{
    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\Column(type="string", length=255)
     */
    private $sinceId;

    /**
     * @ORM\Column(type="string", length=255)
     */
    private $numCommande;

    /**
     * @ORM\Column(type="string", length=255)
     */
    private $numFacture;

    /**
     * @ORM\Column(type="integer")
     */
    private $store;


    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $dateFacture;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $countryCode;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $company;

    /**
     * @ORM\Column(type="decimal", precision=10, scale=2 , nullable=true)
     */
    private $subtotal;

    /**
     * @ORM\Column(type="decimal", precision=10, scale=2 , nullable=true)
     */
    private $shipping;

    /**
     * @ORM\Column(type="decimal", precision=10, scale=2 , nullable=true)
     */
    private $discount;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $compteComptable;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $baseHtMulti;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $baseHt20;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $baseHt55;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $baseHt21;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $baseHt0;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $baseHt6;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $tvaMulti;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $tva20;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $tva55;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $tva0;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $tva21;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $tva6;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $tauxTaxe;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $totalTtc;

    /**
     * @ORM\Column(type="decimal", precision=10, scale=2 , nullable=true)
     */
    private $taxeLivraison;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $montantTaxe;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $discountPercent;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $storeCredit;

    /**
     * @ORM\Column(type="decimal", precision=10, scale=2 , nullable=true)
     */
    private $ecart;

    /**
     * @ORM\Column(type="boolean")
     */
    private $isVisible;



    public function getId(): ?int
    {
        return $this->id;
    }

    public function getSinceId(): ?string
    {
        return $this->sinceId;
    }

    public function setSinceId(string $sinceId): self
    {
        $this->sinceId = $sinceId;

        return $this;
    }

    public function getNumCommande(): ?string
    {
        return $this->numCommande;
    }

    public function setNumCommande(string $numCommande): self
    {
        $this->numCommande = $numCommande;

        return $this;
    }

    public function getNumFacture(): ?string
    {
        return $this->numFacture;
    }

    public function setNumFacture(string $numFacture): self
    {
        $this->numFacture = $numFacture;

        return $this;
    }

    public function getStore(): ?int
    {
        return $this->store;
    }

    public function setStore(int $store): self
    {
        $this->store = $store;

        return $this;
    }


    public function getDateFacture(): ?string
    {
        return $this->dateFacture;
    }

    public function setDateFacture(string $dateFacture): self
    {
        $this->dateFacture = $dateFacture;

        return $this;
    }


    public function getCountryCode(): ?string
    {
        return $this->countryCode;
    }

    public function setCountryCode(?string $countryCode): self
    {
        $this->countryCode = $countryCode;

        return $this;
    }

    public function getCompany(): ?string
    {
        return $this->company;
    }

    public function setCompany(?string $company): self
    {
        $this->company = $company;

        return $this;
    }

    public function getSubtotal(): ?string
    {
        return $this->subtotal;
    }

    public function setSubtotal(string $subtotal): self
    {
        $this->subtotal = $subtotal;

        return $this;
    }

    public function getShipping(): ?string
    {
        return $this->shipping;
    }

    public function setShipping(string $shipping): self
    {
        $this->shipping = $shipping;

        return $this;
    }

    public function getDiscount(): ?string
    {
        return $this->discount;
    }

    public function setDiscount(string $discount): self
    {
        $this->discount = $discount;

        return $this;
    }

    public function getCompteComptable(): ?string
    {
        return $this->compteComptable;
    }

    public function setCompteComptable(?string $compteComptable): self
    {
        $this->compteComptable = $compteComptable;

        return $this;
    }

    public function getBaseHtMulti(): ?string
    {
        return $this->baseHtMulti;
    }

    public function setBaseHtMulti(?string $baseHtMulti): self
    {
        $this->baseHtMulti = $baseHtMulti;

        return $this;
    }

    public function getBaseHt20(): ?string
    {
        return $this->baseHt20;
    }

    public function setBaseHt20(?string $baseHt20): self
    {
        $this->baseHt20 = $baseHt20;

        return $this;
    }

    public function getBaseHt55(): ?string
    {
        return $this->baseHt55;
    }

    public function setBaseHt55(?string $baseHt55): self
    {
        $this->baseHt55 = $baseHt55;

        return $this;
    }

    public function getBaseHt21(): ?string
    {
        return $this->baseHt21;
    }

    public function setBaseHt21(?string $baseHt21): self
    {
        $this->baseHt21 = $baseHt21;

        return $this;
    }

    public function getBaseHt0(): ?string
    {
        return $this->baseHt0;
    }

    public function setBaseHt0(?string $baseHt0): self
    {
        $this->baseHt0 = $baseHt0;

        return $this;
    }

    public function getBaseHt6(): ?string
    {
        return $this->baseHt6;
    }

    public function setBaseHt6(?string $baseHt6): self
    {
        $this->baseHt6 = $baseHt6;

        return $this;
    }

    public function getTvaMulti(): ?string
    {
        return $this->tvaMulti;
    }

    public function setTvaMulti(?string $tvaMulti): self
    {
        $this->tvaMulti = $tvaMulti;

        return $this;
    }

    public function getTva20(): ?string
    {
        return $this->tva20;
    }

    public function setTva20(?string $tva20): self
    {
        $this->tva20 = $tva20;

        return $this;
    }

    public function getTva55(): ?string
    {
        return $this->tva55;
    }

    public function setTva55(?string $tva55): self
    {
        $this->tva55 = $tva55;

        return $this;
    }

    public function getTva0(): ?string
    {
        return $this->tva0;
    }

    public function setTva0(?string $tva0): self
    {
        $this->tva0 = $tva0;

        return $this;
    }

    public function getTva21(): ?string
    {
        return $this->tva21;
    }

    public function setTva21(?string $tva21): self
    {
        $this->tva21 = $tva21;

        return $this;
    }

    public function getTva6(): ?string
    {
        return $this->tva6;
    }

    public function setTva6(?string $tva6): self
    {
        $this->tva6 = $tva6;

        return $this;
    }

    public function getTauxTaxe(): ?string
    {
        return $this->tauxTaxe;
    }

    public function setTauxTaxe(?string $tauxTaxe): self
    {
        $this->tauxTaxe = $tauxTaxe;

        return $this;
    }

    public function getTotalTtc(): ?string
    {
        return $this->totalTtc;
    }

    public function setTotalTtc(?string $totalTtc): self
    {
        $this->totalTtc = $totalTtc;

        return $this;
    }

    public function getTaxeLivraison(): ?string
    {
        return $this->taxeLivraison;
    }

    public function setTaxeLivraison(?string $taxeLivraison): self
    {
        $this->taxeLivraison = $taxeLivraison;

        return $this;
    }

    public function getMontantTaxe(): ?string
    {
        return $this->montantTaxe;
    }

    public function setMontantTaxe(?string $montantTaxe): self
    {
        $this->montantTaxe = $montantTaxe;

        return $this;
    }

    public function getDiscountPercent(): ?string
    {
        return $this->discountPercent;
    }

    public function setDiscountPercent(?string $discountPercent): self
    {
        $this->discountPercent = $discountPercent;

        return $this;
    }

    public function getStoreCredit(): ?string
    {
        return $this->storeCredit;
    }

    public function setStoreCredit(?string $storeCredit): self
    {
        $this->storeCredit = $storeCredit;

        return $this;
    }

    public function getEcart(): ?string
    {
        return $this->ecart;
    }

    public function setEcart(?string $ecart): self
    {
        $this->ecart = $ecart;

        return $this;
    }

    public function getIsVisible(): ?bool
    {
        return $this->isVisible;
    }

    public function setIsVisible(bool $isVisible): self
    {
        $this->isVisible = $isVisible;

        return $this;
    }

}
