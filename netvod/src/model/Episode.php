<?php
namespace netvod\model;

class Episode
{
    public int $id;
    public int $numero;
    public string $titre;
    public string $resume;
    public int $duree;
    public string $file;
    public int $serie_id;

    public function __toString(): string
    {
        return "<div style='border:1px solid #aaa; margin:8px; padding:8px;'>
                    <strong>Épisode {$this->numero} : {$this->titre}</strong><br>
                    <em>Durée : {$this->duree} min</em><br>
                    <p>{$this->resume}</p>
                    <a href='index.php?action=episode&id={$this->id}'>▶ Voir</a>
                </div>";
    }
}
