<?php

namespace App\Entity;

use App\Repository\DebInvoicesRepository;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass=DebInvoicesRepository::class)
 */
class DebInvoices
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
    private $pays_destination;

    /**
     * @ORM\Column(type="string", length=255)
     */
    private $regime_statique;

    /**
     * @ORM\Column(type="string", length=255)
     */
    private $nature_transport;

    /**
     * @ORM\Column(type="float")
     */
    private $valeur_ht;

    /**
     * @ORM\Column(type="float")
     */
    private $masse_kg;

    /**
     * @ORM\Column(type="integer")
     */
    private $unite_supplementaire;

    /**
     * @ORM\Column(type="integer")
     */
    private $mode_transport;

    /**
     * @ORM\Column(type="string", length=255)
     */
    private $departement_arrivee;

    /**
     * @ORM\Column(type="string", length=255)
     */
    private $tva_intracom;

    /**
     * @ORM\Column(type="string", length=255)
     */
    private $detail_nommenclature;

    /**
     * @ORM\Column(type="string", length=255)
     */
    private $num_facture;

    /**
     * @ORM\Column(type="string", length=255)
     */
    private $nom_client;

    /**
     * @ORM\Column(type="string", length=255)
     */
    private $date_facture;

    /**
     * @ORM\Column(type="bigint")
     */
    private $order_id;

    /**
     * @ORM\Column(type="integer")
     */
    private $store_id;

    /**
     * @ORM\Column(type="string", length=255)
     */
    private $type_id;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getPaysDestination(): ?string
    {
        return $this->pays_destination;
    }

    public function setPaysDestination(string $pays_destination): self
    {
        $this->pays_destination = $pays_destination;

        return $this;
    }

    public function getRegimeStatique(): ?string
    {
        return $this->regime_statique;
    }

    public function setRegimeStatique(string $regime_statique): self
    {
        $this->regime_statique = $regime_statique;

        return $this;
    }

    public function getNatureTransport(): ?string
    {
        return $this->nature_transport;
    }

    public function setNatureTransport(string $nature_transport): self
    {
        $this->nature_transport = $nature_transport;

        return $this;
    }

    public function getValeurHt(): ?float
    {
        return $this->valeur_ht;
    }

    public function setValeurHt(float $valeur_ht): self
    {
        $this->valeur_ht = $valeur_ht;

        return $this;
    }

    public function getMasseKg(): ?float
    {
        return $this->masse_kg;
    }

    public function setMasseKg(float $masse_kg): self
    {
        $this->masse_kg = $masse_kg;

        return $this;
    }

    public function getUniteSupplementaire(): ?int
    {
        return $this->unite_supplementaire;
    }

    public function setUniteSupplementaire(int $unite_supplementaire): self
    {
        $this->unite_supplementaire = $unite_supplementaire;

        return $this;
    }

    public function getModeTransport(): ?int
    {
        return $this->mode_transport;
    }

    public function setModeTransport(int $mode_transport): self
    {
        $this->mode_transport = $mode_transport;

        return $this;
    }

    public function getDepartementArrivee(): ?string
    {
        return $this->departement_arrivee;
    }

    public function setDepartementArrivee(string $departement_arrivee): self
    {
        $this->departement_arrivee = $departement_arrivee;

        return $this;
    }

    public function getTvaIntracom(): ?string
    {
        return $this->tva_intracom;
    }

    public function setTvaIntracom(string $tva_intracom): self
    {
        $this->tva_intracom = $tva_intracom;

        return $this;
    }

    public function getDetailNommenclature(): ?string
    {
        return $this->detail_nommenclature;
    }

    public function setDetailNommenclature(string $detail_nommenclature): self
    {
        $this->detail_nommenclature = $detail_nommenclature;

        return $this;
    }

    public function getNumFacture(): ?string
    {
        return $this->num_facture;
    }

    public function setNumFacture(string $num_facture): self
    {
        $this->num_facture = $num_facture;

        return $this;
    }

    public function getNomClient(): ?string
    {
        return $this->nom_client;
    }

    public function setNomClient(string $nom_client): self
    {
        $this->nom_client = $nom_client;

        return $this;
    }

    public function getDateFacture(): ?string
    {
        return $this->date_facture;
    }

    public function setDateFacture(string $date_facture): self
    {
        $this->date_facture = $date_facture;

        return $this;
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

    public function getOrderId(): ?string
    {
        return $this->order_id;
    }

    public function setOrderId(string $order_id): self
    {
        $this->order_id = $order_id;

        return $this;
    }

    public function getStoreId(): ?int
    {
        return $this->store_id;
    }

    public function setStoreId(int $store_id): self
    {
        $this->store_id = $store_id;

        return $this;
    }

    public function getTypeId(): ?string
    {
        return $this->type_id;
    }

    public function setTypeId(string $type_id): self
    {
        $this->type_id = $type_id;

        return $this;
    }
}
