<?php

namespace App\Entity;

use App\Repository\CaFactureRepository;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass=CaFactureRepository::class)
 */
class CaFacture
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
     * @ORM\Column(type="decimal", precision=10, scale=2, nullable=true)
     */
    private $customerBalance;

    /**
     * @ORM\Column(type="decimal", precision=10, scale=2, nullable=true)
     */
    private $giftCards;

    /**
     * @ORM\Column(type="decimal", precision=10, scale=2, nullable=true)
     */
    private $rewardCurrency;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $method;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $dateFacture;

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

    public function getCustomerBalance(): ?string
    {
        return $this->customerBalance;
    }

    public function setCustomerBalance(?string $customerBalance): self
    {
        $this->customerBalance = $customerBalance;

        return $this;
    }

    public function getGiftCards(): ?string
    {
        return $this->giftCards;
    }

    public function setGiftCards(?string $giftCards): self
    {
        $this->giftCards = $giftCards;

        return $this;
    }

    public function getRewardCurrency(): ?string
    {
        return $this->rewardCurrency;
    }

    public function setRewardCurrency(?string $rewardCurrency): self
    {
        $this->rewardCurrency = $rewardCurrency;

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
