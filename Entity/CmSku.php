<?php

namespace App\Entity;

use App\Repository\CmSkuRepository;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass=CmSkuRepository::class)
 */
class CmSku
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
    private $numCreditmemo;


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
    private $rowTotalTax;


    /**
     * @ORM\Column(type="decimal", precision=10, scale=2, nullable=true)
     */
    private $tax;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $dateCreditmemo;

    /**
     * @ORM\Column(type="string", length=255)
     */
    private $sku;

    /**
     * @ORM\Column(type="string", length=255)
     */
    private $productId;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $name;


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

    public function getNumCreditmemo(): ?string
    {
        return $this->numCreditmemo;
    }

    public function setNumCreditmemo(?string $numCreditmemo): self
    {
        $this->numCreditmemo = $numCreditmemo;

        return $this;
    }

    public function getRowTotalTax(): ?string
    {
        return $this->rowTotalTax;
    }

    public function setRowTotalTax(string $rowTotalTax): self
    {
        $this->rowTotalTax = $rowTotalTax;

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


    public function getDateCreditmemo(): ?string
    {
        return $this->dateCreditmemo;
    }

    public function setDateCreditmemo(?string $dateCreditmemo): self
    {
        $this->dateCreditmemo = $dateCreditmemo;

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

    public function setProductId(string $productId): self
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
    public function getTax(): ?string
    {
        return $this->tax;
    }

    public function setTax(?string $tax): self
    {
        $this->tax = $tax;

        return $this;
    }
}
