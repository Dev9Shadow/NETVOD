<?php
namespace netvod\entity;

class Episode
{
    public int $id;
    public ?int $id_serie = null;
    public ?int $numero = null;
    public ?string $titre = null;
    public ?string $resume = null;
    public ?int $duree = null;
    public ?string $file = null;


    public function __toString(): string
    {
        $duree = $this->duree ?? 0;
        $resume = $this->resume ?? 'Pas de résumé disponible';
        
        return "<div style='border:1px solid #ccc; margin:5px; padding:10px;'>
                    <h4>Épisode {$this->numero} : {$this->titre}</h4>
                    <p>{$resume}</p>
                    <small>Durée : {$duree} min</small>
                </div>";
    }
}