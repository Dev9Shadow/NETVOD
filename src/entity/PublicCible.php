<?php
namespace netvod\entity;

class PublicCible
{
    public int $id;
    public string $nom;

    public function __toString(): string
    {
        return $this->nom;
    }
}