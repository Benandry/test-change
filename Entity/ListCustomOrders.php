<?php

namespace App\Entity;

use App\Repository\ListCustomOrdersRepository;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass=ListCustomOrdersRepository::class)
 */
class ListCustomOrders
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
    private $dateCommande;

    /**
     * @ORM\Column(type="string", length=255)
     */
    private $numCommande;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $numTracking;

    /**
     * @ORM\Column(type="string", length=255)
     */

    private $taille;

    
    /**
     * @ORM\Column(type="string", length=255)
     */
    private $sku;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $envoi;

    /**
     * @ORM\Column(type="string", length=255)
     */
    private $adress;

    /**
     * @ORM\Column(type="string", length=255)
     */
    private $prenomClient;

    /**
     * @ORM\Column(type="string", length=255)
     */
    private $nomClient;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $imgBack;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $imgFront;

    /**
     * @ORM\Column(type="string", length=255)
     */
    private $sinceId;

    /**
     * @ORM\Column(type="integer")
     */
    private $store;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $status;

    public function getId(): ?int
    {
        return $this->id;
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

    public function getNumCommande(): ?string
    {
        return $this->numCommande;
    }

    public function setNumCommande(string $numCommande): self
    {
        $this->numCommande = $numCommande;

        return $this;
    }

    public function getNumTracking(): ?string
    {
        return $this->numTracking;
    }

    public function setNumTracking(?string $numTracking): self
    {
        $this->numTracking = $numTracking;

        return $this;
    }

    public function getTaille(): ?string
    {
        return $this->taille;
    }

    public function setTaille(string $taille): self
    {
        $this->taille = $taille;

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

    public function getEnvoi(): ?string
    {
        return $this->envoi;
    }

    public function setEnvoi(?string $envoi): self
    {
        $this->envoi = $envoi;

        return $this;
    }

    public function getAdress(): ?string
    {
        return $this->adress;
    }

    public function setAdress(string $adress): self
    {
        $this->adress = $adress;

        return $this;
    }

    public function getPrenomClient(): ?string
    {
        return $this->prenomClient;
    }

    public function setPrenomClient(string $prenomClient): self
    {
        $this->prenomClient = $prenomClient;

        return $this;
    }

    public function getNomClient(): ?string
    {
        return $this->nomClient;
    }

    public function setNomClient(string $nomClient): self
    {
        $this->nomClient = $nomClient;

        return $this;
    }

    public function getImgBack(): ?string
    {
        return $this->imgBack;
    }

    public function setImgBack(?string $imgBack): self
    {
        $this->imgBack = $imgBack;

        return $this;
    }

    public function getImgFront(): ?string
    {
        return $this->imgFront;
    }

    public function setImgFront(?string $imgFront): self
    {
        $this->imgFront = $imgFront;

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

    public function getStore(): ?int
    {
        return $this->store;
    }

    public function setStore(int $store): self
    {
        $this->store = $store;

        return $this;
    }

    public function getStatus(): ?string
    {
        return $this->status;
    }

    public function setStatus(?string $status): self
    {
        $this->status = $status;

        return $this;
    }
}
