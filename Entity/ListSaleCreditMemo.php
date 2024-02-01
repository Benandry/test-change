<?php

namespace App\Entity;

use App\Repository\ListSaleCreditMemoRepository;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass=ListSaleCreditMemoRepository::class)
 */
class ListSaleCreditMemo
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
    private $numCommande;

    /**
     * @ORM\Column(type="string", length=255)
     */
    private $numCreditmemo;

    /**
     * @ORM\Column(type="string", length=255)
     */
    private $dateCreditmemo;

    /**
     * @ORM\Column(type="string", length=255)
     */
    private $status;

    /**
     * @ORM\Column(type="integer")
     */
    private $store;

    /**
     * @ORM\Column(type="string", length=255)
     */
    private $dateCommande;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $nomClient;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $emailClient;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $idClient;

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
     * @ORM\Column(type="string", length=255)
     */
    private $sinceId;

    /**
     * @ORM\Column(type="string", length=255)
     */
    private $refundId;

    /**
     * @ORM\Column(type="smallint", nullable=true)
     */
    private $isVisible;

    public function getId(): ?int
    {
        return $this->id;
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

    public function getNumCreditmemo(): ?string
    {
        return $this->numCreditmemo;
    }

    public function setNumCreditmemo(string $numCreditmemo): self
    {
        $this->numCreditmemo = $numCreditmemo;

        return $this;
    }

    public function getDateCreditmemo(): ?string
    {
        return $this->dateCreditmemo;
    }

    public function setDateCreditmemo(string $dateCreditmemo): self
    {
        $this->dateCreditmemo = $dateCreditmemo;

        return $this;
    }

    public function getStatus(): ?string
    {
        return $this->status;
    }

    public function setStatus(string $status): self
    {
        $this->status = $status;

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

    public function getDateCommande(): ?string
    {
        return $this->dateCommande;
    }

    public function setDateCommande(string $dateCommande): self
    {
        $this->dateCommande = $dateCommande;

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

    public function getEmailClient(): ?string
    {
        return $this->emailClient;
    }

    public function setEmailClient(?string $emailClient): self
    {
        $this->emailClient = $emailClient;

        return $this;
    }

    public function getIdClient(): ?string
    {
        return $this->idClient;
    }

    public function setIdClient(?string $idClient): self
    {
        $this->idClient = $idClient;

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

    public function setSubtotal(?string $subtotal): self
    {
        $this->subtotal = $subtotal;

        return $this;
    }

    public function getShipping(): ?string
    {
        return $this->shipping;
    }

    public function setShipping(?string $shipping): self
    {
        $this->shipping = $shipping;

        return $this;
    }

    public function getDiscount(): ?string
    {
        return $this->discount;
    }

    public function setDiscount(?string $discount): self
    {
        $this->discount = $discount;

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

    public function getRefundId(): ?string
    {
        return $this->refundId;
    }

    public function setRefundId(string $refundId): self
    {
        $this->refundId = $refundId;

        return $this;
    }

    public function getIsVisible(): ?int
    {
        return $this->isVisible;
    }

    public function setIsVisible(?int $isVisible): self
    {
        $this->isVisible = $isVisible;

        return $this;
    }
}
