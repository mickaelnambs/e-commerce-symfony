<?php

namespace App\Entity;

use Symfony\Component\Validator\Constraints as Assert;

class ProductSearch
{
    /**
     * @Assert\Length(
     *      min=3,
     *      minMessage="La marque doit faire 3 caractères minimun"
     * )
     *
     * @var string
     */
    private $equalMark;

    /**
     * @Assert\Range(
     *      min=10000,
     *      minMessage="Le prix minimun d'un produit est 10000 Ar !"
     * )
     *
     * @var int
     */
    private $equalPrice;

    public function getEqualMark()
    {
        return $this->equalMark;
    }

    public function setEqualMark($equalMark)
    {
        $this->equalMark = $equalMark;

        return $this;
    }

    public function getEqualPrice()
    {
        return $this->equalPrice;
    }

    public function setEqualPrice($equalPrice)
    {
        $this->equalPrice = $equalPrice;

        return $this;
    }
}
