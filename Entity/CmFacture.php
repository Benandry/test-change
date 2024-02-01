<?php

namespace App\Entity;

use App\Repository\CmFactureRepository;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass=CmFactureRepository::class)
 */
class CmFacture
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
    private $numCreditmemo;


    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $nomClient;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $billingClient;


    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $dateCreditmemo;

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

    public function getNumCreditmemo(): ?string
    {
        return $this->numCreditmemo;
    }

    public function setNumCreditmemo(?string $numCreditmemo): self
    {
        $this->numCreditmemo = $numCreditmemo;

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

    public function getBillingClient(): ?string
    {
        return $this->billingClient;
    }

    public function setBillingClient(?string $billingClient): self
    {
        $this->billingClient = $billingClient;

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

}
