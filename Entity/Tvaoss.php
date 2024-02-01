<?php

namespace App\Entity;

use App\Repository\TvaossRepository;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass=TvaossRepository::class)
 */
class Tvaoss
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
    private $date_facture;

    /**
     * @ORM\Column(type="string", length=255)
     */
    private $num_facture;

    /**
     * @ORM\Column(type="string", length=255)
     */
    private $type_operation;

    /**
     * @ORM\Column(type="string", length=255)
     */
    private $type_bien;

    /**
     * @ORM\Column(type="string", length=255)
     */
    private $type_service;

    /**
     * @ORM\Column(type="smallint")
     */
    private $qty;

    /**
     * @ORM\Column(type="float")
     */
    private $prix_unitaire;

    /**
     * @ORM\Column(type="float")
     */
    private $montant_total_ht;

    /**
     * @ORM\Column(type="float")
     */
    private $taux_tva;

    /**
     * @ORM\Column(type="float")
     */
    private $montant_tva;

    /**
     * @ORM\Column(type="float")
     */
    private $montant_ttc;

    /**
     * @ORM\Column(type="string", length=255)
     */
    private $devise;

    /**
     * @ORM\Column(type="string", length=255)
     */
    private $date_livraison;

    /**
     * @ORM\Column(type="string", length=255)
     */
    private $pays_depart;

    /**
     * @ORM\Column(type="string", length=255)
     */
    private $pays_arrivee;

    /**
     * @ORM\Column(type="text")
     */
    private $client_addresse;

    /**
     * @ORM\Column(type="string", length=255)
     */
    private $nom_client;

    /**
     * @ORM\Column(type="string", length=255)
     */
    private $date_paiement;

    /**
     * @ORM\Column(type="float")
     */
    private $montant_paiement;

    /**
     * @ORM\Column(type="float")
     */
    private $accompte;

    /**
     * @ORM\Column(type="string", length=255)
     */
    private $lien_facture;

    /**
     * @ORM\Column(type="smallint")
     */
    private $store;

    /**
     * @ORM\Column(type="bigint")
     */
    private $order_id;

    /**
     * @ORM\Column(type="string", length=255)
     */
    private $type_id;

    public function getId(): ?int
    {
        return $this->id;
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

    public function getNumFacture(): ?string
    {
        return $this->num_facture;
    }

    public function setNumFacture(string $num_facture): self
    {
        $this->num_facture = $num_facture;

        return $this;
    }

    public function getTypeOperation(): ?string
    {
        return $this->type_operation;
    }

    public function setTypeOperation(string $type_operation): self
    {
        $this->type_operation = $type_operation;

        return $this;
    }

    public function getTypeBien(): ?string
    {
        return $this->type_bien;
    }

    public function setTypeBien(string $type_bien): self
    {
        $this->type_bien = $type_bien;

        return $this;
    }

    public function getTypeService(): ?string
    {
        return $this->type_service;
    }

    public function setTypeService(string $type_service): self
    {
        $this->type_service = $type_service;

        return $this;
    }

    public function getQty(): ?int
    {
        return $this->qty;
    }

    public function setQty(int $qty): self
    {
        $this->qty = $qty;

        return $this;
    }

    public function getPrixUnitaire(): ?float
    {
        return $this->prix_unitaire;
    }

    public function setPrixUnitaire(float $prix_unitaire): self
    {
        $this->prix_unitaire = $prix_unitaire;

        return $this;
    }

    public function getMontantTotalHt(): ?float
    {
        return $this->montant_total_ht;
    }

    public function setMontantTotalHt(float $montant_total_ht): self
    {
        $this->montant_total_ht = $montant_total_ht;

        return $this;
    }

    public function getTauxTva(): ?float
    {
        return $this->taux_tva;
    }

    public function setTauxTva(float $taux_tva): self
    {
        $this->taux_tva = $taux_tva;

        return $this;
    }

    public function getMontantTva(): ?float
    {
        return $this->montant_tva;
    }

    public function setMontantTva(float $montant_tva): self
    {
        $this->montant_tva = $montant_tva;

        return $this;
    }

    public function getMontantTtc(): ?float
    {
        return $this->montant_ttc;
    }

    public function setMontantTtc(float $montant_ttc): self
    {
        $this->montant_ttc = $montant_ttc;

        return $this;
    }

    public function getDevise(): ?string
    {
        return $this->devise;
    }

    public function setDevise(string $devise): self
    {
        $this->devise = $devise;

        return $this;
    }

    public function getDateLivraison(): ?string
    {
        return $this->date_livraison;
    }

    public function setDateLivraison(string $date_livraison): self
    {
        $this->date_livraison = $date_livraison;

        return $this;
    }

    public function getPaysDepart(): ?string
    {
        return $this->pays_depart;
    }

    public function setPaysDepart(string $pays_depart): self
    {
        $this->pays_depart = $pays_depart;

        return $this;
    }

    public function getPaysArrivee(): ?string
    {
        return $this->pays_arrivee;
    }

    public function setPaysArrivee(string $pays_arrivee): self
    {
        $this->pays_arrivee = $pays_arrivee;

        return $this;
    }

    public function getClientAddresse(): ?string
    {
        return $this->client_addresse;
    }

    public function setClientAddresse(string $client_addresse): self
    {
        $this->client_addresse = $client_addresse;

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

    public function getDatePaiement(): ?string
    {
        return $this->date_paiement;
    }

    public function setDatePaiement(string $date_paiement): self
    {
        $this->date_paiement = $date_paiement;

        return $this;
    }

    public function getMontantPaiement(): ?float
    {
        return $this->montant_paiement;
    }

    public function setMontantPaiement(float $montant_paiement): self
    {
        $this->montant_paiement = $montant_paiement;

        return $this;
    }

    public function getAccompte(): ?float
    {
        return $this->accompte;
    }

    public function setAccompte(float $accompte): self
    {
        $this->accompte = $accompte;

        return $this;
    }

    public function getLienFacture(): ?string
    {
        return $this->lien_facture;
    }

    public function setLienFacture(string $lien_facture): self
    {
        $this->lien_facture = $lien_facture;

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
