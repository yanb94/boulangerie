<?php 

namespace Grav\Plugin\Panier;

class Order
{
    private string $cookie;
    private string $statut;
    private float $tva;
    private array $rows = [];

    private float $price_ht = 0;
    private float $vat = 0;
    private float $price_ttc = 0;

    const BEGIN = "begin";
    const VALIDATE = "validate";
    const FINISH = "finish";

    public function __construct(string $cookie, string $statut, float $tva)
    {
        $this->cookie = $cookie;
        $this->statut = $statut;
        $this->tva = $tva;
    }

    public function addRow(OrderRow $orderRow): self
    {
        $rowPrice = $orderRow->getQuantity() * $orderRow->getPrice();

        $this->price_ht += $rowPrice;
        $this->vat += $rowPrice * ($this->tva / 100);
        $this->price_ttc = $this->price_ht + $this->vat;

        $this->rows[] = $orderRow;
        return $this;
    }

    public function getRows(): array
    {
        return $this->rows;
    }

	public function getCookie():string {
		return $this->cookie;
	}

	public function setCookie(string $cookie):self {
        $this->cookie = $cookie;
        return $this;
	}

	public function getStatut():string {
		return $this->statut;
	}

	public function setStatut(string $statut):self {
        $this->statut = $statut;
        return $this;
	}

	public function  getTva():float {
		return $this->tva;
	}

	public function setTva(float $tva):self {
        $this->tva = $tva;
        return $this;
    }

    public function getPrice_ht(): float
    {
        return $this->price_ht;
    }

    public function getPrice_ttc(): float
    {
        return $this->price_ttc;
    }

    public function getVat(): float
    {
        return $this->vat;
    }

}