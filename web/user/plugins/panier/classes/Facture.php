<?php

namespace Grav\Plugin\Panier;

use DateTime;

class Facture
{
    private string $id;
    private string $firstname;
    private string $lastname;
    private string $email;
    private string $idStripe;
    private string $statut;
    private float $tva;
    private array $rows = [];
    private int $numero;

    private float $price_ht = 0;
    private float $vat = 0;
    private float $price_ttc = 0;

    private DateTime $created_at;

    const PAYED = "payed";
    const DELIVRED = "delivred";
    const REFUND = "refund";


    public function __construct(?string $id = null, ?string $statut = null)
    {

        $this->id = $id != null ? $id : uniqid();
        $this->statut = $statut != null ? $statut : self::PAYED;
        
    }

    public function dataFromOrder(Order $order):void
    {
        $this->setTva($order->getTva());
        $this->setRows($order->getRows());

        $this->setPrice_ht($order->getPrice_ht());
        $this->setPrice_ttc($order->getPrice_ttc());
        $this->setVat($order->getVat());

    }


    /**
     * Get the value of id
     */ 
    public function getId()
    {
        return $this->id;
    }

    /**
     * Set the value of id
     *
     * @return  self
     */ 
    public function setId($id)
    {
        $this->id = $id;

        return $this;
    }

    /**
     * Get the value of firstname
     */ 
    public function getFirstname()
    {
        return $this->firstname;
    }

    /**
     * Set the value of firstname
     *
     * @return  self
     */ 
    public function setFirstname($firstname)
    {
        $this->firstname = $firstname;

        return $this;
    }

    /**
     * Get the value of lastname
     */ 
    public function getLastname()
    {
        return $this->lastname;
    }

    /**
     * Set the value of lastname
     *
     * @return  self
     */ 
    public function setLastname($lastname)
    {
        $this->lastname = $lastname;

        return $this;
    }

    /**
     * Get the value of email
     */ 
    public function getEmail()
    {
        return $this->email;
    }

    /**
     * Set the value of email
     *
     * @return  self
     */ 
    public function setEmail($email)
    {
        $this->email = $email;

        return $this;
    }

    /**
     * Get the value of idStripe
     */ 
    public function getIdStripe()
    {
        return $this->idStripe;
    }

    /**
     * Set the value of idStripe
     *
     * @return  self
     */ 
    public function setIdStripe($idStripe)
    {
        $this->idStripe = $idStripe;

        return $this;
    }

    /**
     * Get the value of statut
     */ 
    public function getStatut()
    {
        return $this->statut;
    }

    /**
     * Set the value of statut
     *
     * @return  self
     */ 
    public function setStatut($statut)
    {
        $this->statut = $statut;

        return $this;
    }

    /**
     * Get the value of tva
     */ 
    public function getTva()
    {
        return $this->tva;
    }

    /**
     * Set the value of tva
     *
     * @return  self
     */ 
    public function setTva($tva)
    {
        $this->tva = $tva;

        return $this;
    }

    /**
     * Get the value of rows
     */ 
    public function getRows()
    {
        return $this->rows;
    }

    /**
     * Set the value of rows
     *
     * @return  self
     */ 
    public function setRows($rows)
    {
        $this->rows = $rows;

        return $this;
    }

    /**
     * Get the value of price_ht
     */ 
    public function getPrice_ht()
    {
        return $this->price_ht;
    }

    /**
     * Set the value of price_ht
     *
     * @return  self
     */ 
    public function setPrice_ht($price_ht)
    {
        $this->price_ht = $price_ht;

        return $this;
    }

    /**
     * Get the value of vat
     */ 
    public function getVat()
    {
        return $this->vat;
    }

    /**
     * Set the value of vat
     *
     * @return  self
     */ 
    public function setVat($vat)
    {
        $this->vat = $vat;

        return $this;
    }

    /**
     * Get the value of price_ttc
     */ 
    public function getPrice_ttc()
    {
        return $this->price_ttc;
    }

    /**
     * Set the value of price_ttc
     *
     * @return  self
     */ 
    public function setPrice_ttc($price_ttc)
    {
        $this->price_ttc = $price_ttc;

        return $this;
    }

    /**
     * Get the value of numero
     */ 
    public function getNumero()
    {
        return $this->numero;
    }

    /**
     * Set the value of numero
     *
     * @return  self
     */ 
    public function setNumero($numero)
    {
        $this->numero = $numero;

        return $this;
    }

    public function factureNumero()
    {
        return str_pad($this->numero,12,"0",STR_PAD_LEFT);
    }

    /**
     * Get the value of created_at
     */ 
    public function getCreated_at(): DateTime
    {
        return $this->created_at;
    }

    /**
     * Set the value of created_at
     *
     * @return  self
     */ 
    public function setCreated_at(DateTime $created_at)
    {
        $this->created_at = $created_at;

        return $this;
    }

    public function statutText(): ?string
    {
        switch ($this->statut) {
            case self::PAYED:
                return "En attente de retrait";
                break;
            case self::DELIVRED:
                return "Délivré au client";
                break;
            case self::REFUND:
                return "Remboursé";
                break;
            default:
                break;
        }
    }
}