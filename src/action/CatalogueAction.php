<?php
namespace netvod\action;

use netvod\repository\ConnectionFactory;
use netvod\repository\SerieRepository;
use netvod\renderer\Layout;

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
            $html .= "<div class='series-grid'>";
            foreach ($series as $s) {
                $descriptif = $s->descriptif ?? 'Pas de description disponible';
                $annee = $s->annee ?? 'N/A';
                
                $html .= "<div class='card'>
                            <h3><a href='index.php?action=serie&id={$s->id}'>" . htmlspecialchars($s->titre) . "</a></h3>
                            <p>" . htmlspecialchars($descriptif) . "</p>
                            <small>Année : {$annee}</small>
                          </div>";
            }
            $html .= "</div>";
        }

        return Layout::render($html, "Catalogue - NETVOD");
    }
}