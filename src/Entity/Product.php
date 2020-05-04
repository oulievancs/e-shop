<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;
use JsonSerializable;

/**
 * @ORM\Entity(repositoryClass="App\Repository\ProductRepository")
 */
class Product implements JsonSerializable
{
    /**
     * @ORM\Id()
     * @ORM\GeneratedValue(strategy="AUTO")
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\Column(type="string", length=255)
     */
    private $ProductDescr;

    /**
     * @ORM\Column(type="float")
     */
    private $ProductPrice;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\Store", inversedBy="Products")
     */
    private $Store;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getProductDescr(): ?string
    {
        return $this->ProductDescr;
    }

    public function setProductDescr(string $ProductDescr): self
    {
        $this->ProductDescr = $ProductDescr;

        return $this;
    }

    public function getProductPrice(): ?float
    {
        return $this->ProductPrice;
    }

    public function setProductPrice(float $ProductPrice): self
    {
        $this->ProductPrice = $ProductPrice;

        return $this;
    }

    public function getStore(): ?Store
    {
        return $this->Store;
    }

    public function setStore(?Store $Store): self
    {
        $this->Store = $Store;

        return $this;
    }

    public function jsonSerialize() {
        return array(
            "id" => $this->id,
            "productName" => $this->ProductDescr,
            "productPrice" => $this->ProductPrice
        );
    }
}
