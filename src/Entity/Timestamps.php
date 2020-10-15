<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;

trait Timestamps
{
    /** 
     * @ORM\Column(type="datetime")
     */
    private $createdAt;

    private $updatedAt;

    /**
     * @ORM\PrePersist
     * @ORM\PreUpdate
     * 
     * @return void
     */
    public function prePersist()
    {
        if (empty($this->createdAt)) {
            $this->createdAt = new \DateTime();
        }
    }

    public function getCreatedAt()
    {
        return $this->createdAt;
    }

    public function setCreatedAt($createdAt)
    {
        $this->createdAt = $createdAt;

        return $this;
    }
}
