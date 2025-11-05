<?php
namespace netvod\action;

use netvod\repository\ConnectionFactory;
use netvod\repository\SerieRepository;
use netvod\repository\EpisodeRepository;
use netvod\renderer\Layout;

class SerieAction
{
    public function execute(): string
    {
        ConnectionFactory::setConfig(__DIR__ . '/../../config/db.config.ini');
        
        $id = $_GET['id'] ?? 0;
        
        $serieRepo = new SerieRepository();
        $serie = $serieRepo->findById((int)$id);
        
        if (!$serie) {
            return Layout::render(
                "<h1>Série non trouvée</h1><p><a href='index.php?action=catalogue'>⬅ Retour au catalogue</a></p>",
                "Erreur - NETVOD"
            );
        }
        
        // Récupérer les épisodes
        $episodeRepo = new EpisodeRepository();
        $episodes = $episodeRepo->findBySerie($serie->id);
        
        // Construire le HTML
        $descriptif = $serie->descriptif ?? 'Pas de description disponible';
        $annee = $serie->annee ?? 'N/A';
        $dateAjout = $serie->date_ajout ?? '';
        
        $html = "<div class='card' style='margin-bottom: 30px;'>
                    <h1>" . htmlspecialchars($serie->titre) . "</h1>
                    <p>" . htmlspecialchars($descriptif) . "</p>
                    <small>Année : {$annee} | Ajoutée le : {$dateAjout}</small>
                 </div>";
        
        $html .= "<h2>Épisodes</h2>";
        
        if (empty($episodes)) {
            $html .= "<p>Aucun épisode disponible pour cette série.</p>";
        } else {
            $html .= "<div class='episode-list'>";
            foreach ($episodes as $ep) {
                $titre = htmlspecialchars($ep->titre ?? 'Sans titre');
                $resume = htmlspecialchars($ep->resume ?? 'Pas de résumé');
                $duree = $ep->duree ?? 0;
                $numero = $ep->numero ?? '?';
                
                $html .= "<div class='episode-item'>
                            <h4>Épisode {$numero} : {$titre}</h4>
                            <p>{$resume}</p>
                            <small>Durée : {$duree} min</small>
                            <br>
                            <a href='index.php?action=episode&id={$ep->id}' class='btn' style='margin-top: 10px; display: inline-block;'>▶ Voir</a>
                          </div>";
            }
            $html .= "</div>";
        }
        
        $html .= "<p style='margin-top: 30px;'><a href='index.php?action=catalogue'>⬅ Retour au catalogue</a></p>";
        
        return Layout::render($html, htmlspecialchars($serie->titre) . " - NETVOD");
    }
}