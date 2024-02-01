<?php

namespace App\Entity;

use App\Repository\SufioInvoicesRepository;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass=SufioInvoicesRepository::class)
 */
class SufioInvoices
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
    private $invoice_number;

    /**
     * @ORM\Column(type="datetime")
     */
    private $issue_date;

    /**
     * @ORM\Column(type="float")
     */
    private $subtotal;

    /**
     * @ORM\Column(type="float")
     */
    private $total_excl_tax;

    /**
     * @ORM\Column(type="float")
     */
    private $taxes_total;

    /**
     * @ORM\Column(type="float")
     */
    private $invoice_total;

    /**
     * @ORM\Column(type="float")
     */
    private $tax_rate;

    /**
     * @ORM\Column(type="float")
     */
    private $tax_amount;

    /**
     * @ORM\Column(type="string", length=255)
     */
    private $currency;

    /**
     * @ORM\Column(type="string", length=255)
     */
    private $country_code;

    /**
     * @ORM\Column(type="string", length=255)
     */
    private $payment_method;

    /**
     * @ORM\Column(type="string", length=255)
     */
    private $order_number;

    /**
     * @ORM\Column(type="float")
     */
    private $discount_percent;

    /**
     * @ORM\Column(type="float")
     */
    private $discount_amount;

    /**
     * @ORM\Column(type="float")
     */
    private $shipping;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getInvoiceNumber(): ?string
    {
        return $this->invoice_number;
    }

    public function setInvoiceNumber(string $invoice_number): self
    {
        $this->invoice_number = $invoice_number;

        return $this;
    }

    public function getIssueDate(): ?\DateTimeInterface
    {
        return $this->issue_date;
    }

    public function setIssueDate(\DateTimeInterface $issue_date): self
    {
        $this->issue_date = $issue_date;

        return $this;
    }

    public function getSubtotal(): ?float
    {
        return $this->subtotal;
    }

    public function setSubtotal(float $subtotal): self
    {
        $this->subtotal = $subtotal;

        return $this;
    }

    public function getTotalExclTax(): ?float
    {
        return $this->total_excl_tax;
    }

    public function setTotalExclTax(float $total_excl_tax): self
    {
        $this->total_excl_tax = $total_excl_tax;

        return $this;
    }

    public function getTaxesTotal(): ?float
    {
        return $this->taxes_total;
    }

    public function setTaxesTotal(float $taxes_total): self
    {
        $this->taxes_total = $taxes_total;

        return $this;
    }

    public function getInvoiceTotal(): ?float
    {
        return $this->invoice_total;
    }

    public function setInvoiceTotal(float $invoice_total): self
    {
        $this->invoice_total = $invoice_total;

        return $this;
    }

    public function getTaxRate(): ?float
    {
        return $this->tax_rate;
    }

    public function setTaxRate(float $tax_rate): self
    {
        $this->tax_rate = $tax_rate;

        return $this;
    }

    public function getTaxAmount(): ?float
    {
        return $this->tax_amount;
    }

    public function setTaxAmount(float $tax_amount): self
    {
        $this->tax_amount = $tax_amount;

        return $this;
    }

    public function getCurrency(): ?string
    {
        return $this->currency;
    }

    public function setCurrency(string $currency): self
    {
        $this->currency = $currency;

        return $this;
    }

    public function getCountryCode(): ?string
    {
        return $this->country_code;
    }

    public function setCountryCode(string $country_code): self
    {
        $this->country_code = $country_code;

        return $this;
    }

    public function getPaymentMethod(): ?string
    {
        return $this->payment_method;
    }

    public function setPaymentMethod(string $payment_method): self
    {
        $this->payment_method = $payment_method;

        return $this;
    }

    public function getOrderNumber(): ?string
    {
        return $this->order_number;
    }

    public function setOrderNumber(string $order_number): self
    {
        $this->order_number = $order_number;

        return $this;
    }

    public function getDiscountPercent(): ?float
    {
        return $this->discount_percent;
    }

    public function setDiscountPercent(float $discount_percent): self
    {
        $this->discount_percent = $discount_percent;

        return $this;
    }

    public function getDiscountAmount(): ?float
    {
        return $this->discount_amount;
    }

    public function setDiscountAmount(float $discount_amount): self
    {
        $this->discount_amount = $discount_amount;

        return $this;
    }

    public function getShipping(): ?float
    {
        return $this->shipping;
    }

    public function setShipping(float $shipping): self
    {
        $this->shipping = $shipping;

        return $this;
    }
}
