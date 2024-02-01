<?php

namespace App\Entity;

use App\Repository\CaPaysRepository;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass=CaPaysRepository::class)
 */
class CaPays
{
    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\Column(type="integer")
     */
    private $store;

    /**
     * @ORM\Column(type="string", length=255)
     */
    private $sinceId;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $numCommande;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $numFacture;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $emailClient;

    /**
     * @ORM\Column(type="decimal", precision=10, scale=2)
     */
    private $subtotal;

    /**
     * @ORM\Column(type="decimal", precision=10, scale=2)
     */
    private $shipping;

    /**
     * @ORM\Column(type="decimal", precision=10, scale=2)
     */
    private $discount;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $nomClient;

    /**
     * @ORM\Column(type="integer", nullable=true)
     */
    private $qty;

    /**
     * @ORM\Column(type="decimal", precision=10, scale=2, nullable=true)
     */
    private $subtotalTax;

    /**
     * @ORM\Column(type="decimal", precision=10, scale=2, nullable=true)
     */
    private $shippingTax;

    /**
     * @ORM\Column(type="decimal", precision=10, scale=2, nullable=true)
     */
    private $tax;

    /**
     * @ORM\Column(type="decimal", precision=10, scale=2, nullable=true)
     */
    private $grandTotal;

    /**
     * @ORM\Column(type="decimal", precision=10, scale=2, nullable=true)
     */
    private $shippinInclTax;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $method;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $dateFacture;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $countryId;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $billingCompany;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $shippingCompany;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $shippingRegion;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $billingRegion;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $shippingPostcode;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $shippingCity;

    /**
     * @ORM\Column(type="boolean")
     */
    private $isVisible;

    public function getId(): ?int
    {
        return $this->id;
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

    public function setNumCommande(?string $numCommande): self
    {
        $this->numCommande = $numCommande;

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

    public function getEmailClient(): ?string
    {
        return $this->emailClient;
    }

    public function setEmailClient(?string $emailClient): self
    {
        $this->emailClient = $emailClient;

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

    public function getNomClient(): ?string
    {
        return $this->nomClient;
    }

    public function setNomClient(?string $nomClient): self
    {
        $this->nomClient = $nomClient;

        return $this;
    }

    public function getQty(): ?int
    {
        return $this->qty;
    }

    public function setQty(?int $qty): self
    {
        $this->qty = $qty;

        return $this;
    }

    public function getSubtotalTax(): ?string
    {
        return $this->subtotalTax;
    }

    public function setSubtotalTax(?string $subtotalTax): self
    {
        $this->subtotalTax = $subtotalTax;

        return $this;
    }

    public function getShippingTax(): ?string
    {
        return $this->shippingTax;
    }

    public function setShippingTax(?string $shippingTax): self
    {
        $this->shippingTax = $shippingTax;

        return $this;
    }

    public function getTax(): ?string
    {
        return $this->tax;
    }

    public function setTax(?string $tax): self
    {
        $this->tax = $tax;

        return $this;
    }

    public function getGrandTotal(): ?string
    {
        return $this->grandTotal;
    }

    public function setGrandTotal(?string $grandTotal): self
    {
        $this->grandTotal = $grandTotal;

        return $this;
    }

    public function getShippinInclTax(): ?string
    {
        return $this->shippinInclTax;
    }

    public function setShippinInclTax(?string $shippinInclTax): self
    {
        $this->shippinInclTax = $shippinInclTax;

        return $this;
    }

    public function getMethod(): ?string
    {
        return $this->method;
    }

    public function setMethod(?string $method): self
    {
        $this->method = $method;

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
    public function getCountryId(): ?string
    {
        return $this->countryId;
    }

    public function setCountryId(?string $countryId): self
    {
        $this->countryId = $countryId;

        return $this;
    }

    public function getBillingCompany(): ?string
    {
        return $this->billingCompany;
    }

    public function setBillingCompany(?string $billingCompany): self
    {
        $this->billingCompany = $billingCompany;

        return $this;
    }
    public function getShippingCompany(): ?string
    {
        return $this->shippingCompany;
    }

    public function setShippingCompany(?string $shippingCompany): self
    {
        $this->shippingCompany = $shippingCompany;

        return $this;
    }


    public function setCity(?string $city): self
    {
        $this->city = $city;

        return $this;
    }
    public function getCity(): ?string
    {
        return $this->city;
    }

    public function setShippingPostcode(?string $shippingPostcode): self
    {
        $this->shippingPostcode = $shippingPostcode;

        return $this;
    }
    public function getShippingPostcode(): ?string
    {
        return $this->shippingPostcode;
    }

    public function setShippingCity(?string $shippingCity): self
    {
        $this->shippingCity = $shippingCity;

        return $this;
    }
    public function getShippingCity(): ?string
    {
        return $this->shippingCity;
    }

    public function setShippingRegion(?string $shippingRegion): self
    {
        $this->shippingRegion = $shippingRegion;

        return $this;
    }
    public function getShippingRegion(): ?string
    {
        return $this->shippingRegion;
    }

    public function setBillingRegion(?string $billingRegion): self
    {
        $this->billingRegion = $billingRegion;

        return $this;
    }
    public function getBillingRegion(): ?string
    {
        return $this->billingRegion;
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
