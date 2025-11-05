<?php
namespace netvod\entity;

class Serie
{
    public int $id;
    public string $titre;
    public ?string $descriptif = null;  // Nullable
    public ?string $img = null;         // Nullable
    public ?int $annee = null;          // Nullable
    public ?string $date_ajout = null;  // Nullable
    public ?string $description = null;
    public ?string $genre = null;
    public ?string $image_url = null;

    public function __toString(): string
    {
        $descriptif = $this->descriptif ?? 'Pas de description';
        $annee = $this->annee ?? 'N/A';
        
        return "<div style='border:1px solid #ccc; margin:5px; padding:10px;'>
                    <h3>{$this->titre}</h3>
                    <p>{$descriptif}</p>
                    <small>Année : {$annee}</small>
                </div>";
    }
}