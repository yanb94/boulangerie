<?php

namespace Grav\Plugin\Panier;

use Grav\Plugin\Panier\Product;

class OrderRow
{
    private int $quantity;
    private string $ref;
    private float $price;
    private string $name;
    private ?string $photo;

    public function __construct(int $quantity, string $ref, float $price, string $name, ?string $photo = null)
    {
        $this->quantity = $quantity;
        $this->ref = $ref;
        $this->price = $price;
        $this->name = $name;
        $this->photo = $photo;
    }

    public function getQuantity()
    {
        return $this->quantity;
    }

    public function setQuantity(int $quantity):self
    {
        $this->quantity = $quantity;
        return $this;
    }

    public function getRef()
    {
        return $this->ref;
    }

    public function getPrice(): float
    {
        return $this->price;
    }

    public function getName()
    {
        return $this->name;
    }

    public function setRef(string $ref): self
    {
        $this->ref = $ref;
        return $this;
    }

    public function setPrice(float $price): self
    {
        $this->price = $price;
        return $this;
    }

    public function setName(string $name): self
    {
        $this->name = $name;
        return $this;
    }

    public function getPhoto(): string
    {
        return $this->photo;
    }

    public function setPhoto(?string $photo): self
    {
        $this->photo = $photo;
        return $this;
    }

}