<?php

namespace App\Entity;

use App\Repository\CtAutreRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

/**
* @ORM\Entity(repositoryClass=CtAutreRepository::class)
*/
class CtAutre
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
    private $nom ;

    /**
     * @ORM\Column(type="string", length=255)
     */
    private $attribut;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getNom(): ?string
    {
        return strtoupper($this->nom);
    }

    public function setNom(string $nom): self
    {
        $this->nom = strtoupper($nom);

        return $this;
    }

    public function getAttribut(): ?string
    {
        return strtoupper($this->attribut);
    }

    public function setAttribut(string $attribut): self
    {
        $this->attribut = strtoupper($attribut);

        return $this;
    }

    /**
    * toString
    * @return string
    */
    public function __toString()
    {
        return $this->getNom();
    }
}
