<?php

namespace App\Entity;

use App\Repository\CaNewSkuRepository;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass=CaNewSkuRepository::class)
 */
class CaNewSku
{
    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $productId;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $numCommande;

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
    private $numFacture;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $typeDeBien;

    /**
     * @ORM\Column(type="string", length=255)
     */
    private $sku;

    /**
     * @ORM\Column(type="string", length=255)
     */
    private $name;

    /**
     * @ORM\Column(type="decimal", precision=10, scale=2, nullable=true)
     */
    private $totalQtyOrder;

    /**
     * @ORM\Column(type="decimal", precision=10, scale=2, nullable=true)
     */
    private $priceHt;

    /**
     * @ORM\Column(type="decimal", precision=10, scale=2, nullable=true)
     */
    private $montantHt;

    /**
     * @ORM\Column(type="decimal", precision=10, scale=2, nullable=true)
     */
    private $montantTTC;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $company;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $companyFacturaation;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $nomClient;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $paysLivraison;

    /**
     * @ORM\Column(type="string", length=255)
     */
    private $sinceId;

    /**
     * @ORM\Column(type="boolean", nullable=true)
     */
    private $isVisible;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $countryCode;


    public function getId(): ?int
    {
        return $this->id;
    }
    public function getData($property)
    {
        return $this->{$property};
    }

    public function setData($property, $value): self
    {

        $this->{$property} = $value;

        return $this;
    }
    public function getProductId(): ?string
    {
        return $this->productId;
    }

    public function setProductId(?string $productId): self
    {
        $this->productId = $productId;

        return $this;
    }

    public function getNumCommande(): ?string
    {
        return $this->numCommande;
    }

    public function setNumCommande(?string $numCommande): self
    {
        $this->numCommande = $numCommande;

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

    public function setDateFacture(?string $dateFacture): self
    {
        $this->dateFacture = $dateFacture;

        return $this;
    }

    public function getNumFacture(): ?string
    {
        return $this->numFacture;
    }

    public function setNumFacture(?string $numFacture): self
    {
        $this->numFacture = $numFacture;

        return $this;
    }

    public function getTypeDeBien(): ?string
    {
        return $this->typeDeBien;
    }

    public function setTypeDeBien(?string $typeDeBien): self
    {
        $this->typeDeBien = $typeDeBien;

        return $this;
    }

    public function getSku(): ?string
    {
        return $this->sku;
    }

    public function setSku(string $sku): self
    {
        $this->sku = $sku;

        return $this;
    }

    public function getName(): ?string
    {
        return $this->name;
    }

    public function setName(string $name): self
    {
        $this->name = $name;

        return $this;
    }

    public function getTotalQtyOrder(): ?string
    {
        return $this->totalQtyOrder;
    }

    public function setTotalQtyOrder(?string $totalQtyOrder): self
    {
        $this->totalQtyOrder = $totalQtyOrder;

        return $this;
    }

    public function getPriceHt(): ?string
    {
        return $this->priceHt;
    }

    public function setPriceHt(?string $priceHt): self
    {
        $this->priceHt = $priceHt;

        return $this;
    }

    public function getMontantHt(): ?string
    {
        return $this->montantHt;
    }

    public function setMontantHt(?string $montantHt): self
    {
        $this->montantHt = $montantHt;

        return $this;
    }

    public function getMontantTTC(): ?string
    {
        return $this->montantTTC;
    }

    public function setMontantTTC(?string $montantTTC): self
    {
        $this->montantTTC = $montantTTC;

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

    public function getCompanyFacturaation(): ?string
    {
        return $this->companyFacturaation;
    }

    public function setCompanyFacturaation(?string $companyFacturaation): self
    {
        $this->companyFacturaation = $companyFacturaation;

        return $this;
    }

    public function getNomClient(): ?string
    {
        return $this->nomClient;
    }

    public function setNomClient(?string $nomClient): self
    {
        $this->nomClient = $nomClient;

        return $this;
    }

    public function getPaysLivraison(): ?string
    {
        return $this->paysLivraison;
    }

    public function setPaysLivraison(?string $paysLivraison): self
    {
        $this->paysLivraison = $paysLivraison;

        return $this;
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

    public function getIsVisible(): ?bool
    {
        return $this->isVisible;
    }

    public function setIsVisible(?bool $isVisible): self
    {
        $this->isVisible = $isVisible;

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
}
