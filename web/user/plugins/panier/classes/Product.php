<?php

namespace Grav\Plugin\Panier;

class Product
{
    private string $ref;
    private float $price;
    private string $name;

    /**
     * Constructor
     *
     * @param string $ref
     * @param float $price
     * @param string $name
     */
    public function __construct(string $ref, float $price, string $name)
    {
        $this->ref = $ref;
        $this->price = $price;
        $this->name = $name;
    }

    public function getRef()
    {
        return $this->ref;
    }

    public function getPrice()
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

}