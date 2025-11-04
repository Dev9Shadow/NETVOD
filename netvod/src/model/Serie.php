<?php
namespace netvod\model;

class Serie
{
    public int $id;
    public string $titre;
    public string $descriptif;
    public string $img;
    public int $annee;
    public string $date_ajout;

    public function __toString(): string
    {
        return "<div style='border:1px solid #ccc; margin:5px; padding:10px;'>
                    <h3>{$this->titre}</h3>
                    <p>{$this->descriptif}</p>
                    <small>Année : {$this->annee}</small>
                </div>";
    }
}
