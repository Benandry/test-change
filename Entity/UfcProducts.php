<?php

namespace App\Entity;

use App\Repository\UfcProductsRepository;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass=UfcProductsRepository::class)
 */
class UfcProducts
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
    private $sku;

    /**
     * @ORM\Column(type="string", length=255)
     */
    private $couleur;

    /**
     * @ORM\Column(type="string", length=255 , nullable=true)
     */
    private $flocage;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $pays;

    public function getId(): ?int
    {
        return $this->id;
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

    public function getCouleur(): ?string
    {
        return $this->couleur;
    }

    public function setCouleur(string $couleur): self
    {
        $this->couelur = $couleur;

        return $this;
    }

    public function getFlocage(): ?string
    {
        return $this->flocage;
    }

    public function setFlocage(string $flocage): self
    {
        $this->flocage = $flocage;

        return $this;
    }

    public function getPays(): ?string
    {
        return $this->pays;
    }

    public function setPays(string $pays): self
    {
        $this->pays = $pays;

        return $this;
    }
}
