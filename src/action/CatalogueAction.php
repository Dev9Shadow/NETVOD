<?php
namespace netvod\action;

use netvod\repository\ConnectionFactory;
use netvod\repository\SerieRepository;

class CatalogueAction
{
    public function execute(): string
    {
        // init connexion BDD
        ConnectionFactory::setConfig(__DIR__ . '/../../config/db.config.ini');

        // récupérer toutes les séries
        $repo   = new SerieRepository();
        $series = $repo->findAll();

        // construire le HTML (chaque série est cliquable)
        $html = "<h1>Catalogue des séries</h1>";

        if (empty($series)) {
            $html .= "<p>Aucune série en base.</p>";
        } else {
            foreach ($series as $s) {
                $html .= "<div style='border:1px solid #ccc; margin:5px; padding:10px;'>
                            <h3><a href='index.php?action=serie&id={$s->id}'>{$s->titre}</a></h3>
                            <p>{$s->descriptif}</p>
                            <small>Année : {$s->annee}</small>
                          </div>";
            }
        }

        $html .= "<p><a href='index.php'>⬅ Retour à l'accueil</a></p>";
        return $html;
    }
}
