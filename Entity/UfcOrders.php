<?php

namespace App\Entity;

use App\Repository\UfcOrdersRepository;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass=UfcOrdersRepository::class)
 */
class UfcOrders
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
     * @ORM\Column(type="decimal", precision=10, scale=2)
     */
    private $subtotal;

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
    private $tax;

    /**
     * @ORM\Column(type="string", length=255)
     */
    private $dateFacture;

    /**
     * @ORM\Column(type="string", length=255)
     */
    private $sku;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $productId;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $name;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $shippingCompany;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $billingCompany;

    /**
     * @ORM\Column(type="boolean")
     */
    private $isVisible;

    /**
     * @ORM\Column(type="integer", nullable=true)
     */
    private $invoice_id;

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

    public function getSubtotal(): ?string
    {
        return $this->subtotal;
    }

    public function setSubtotal(string $subtotal): self
    {
        $this->subtotal = $subtotal;

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

    public function getTax(): ?string
    {
        return $this->tax;
    }

    public function setTax(?string $tax): self
    {
        $this->tax = $tax;

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

    public function getSku(): ?string
    {
        return $this->sku;
    }

    public function setSku(string $sku): self
    {
        $this->sku = $sku;

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

    public function getName(): ?string
    {
        return $this->name;
    }

    public function setName(?string $name): self
    {
        $this->name = $name;

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

    public function getBillingCompany(): ?string
    {
        return $this->billingCompany;
    }

    public function setBillingCompany(?string $billingCompany): self
    {
        $this->billingCompany = $billingCompany;

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

    public function getInvoiceId(): ?int
    {
        return $this->invoice_id;
    }

    public function setInvoiceId(?int $invoice_id): self
    {
        $this->invoice_id = $invoice_id;

        return $this;
    }
}
