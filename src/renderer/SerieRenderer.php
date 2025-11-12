<?php
namespace netvod\renderer;

use netvod\entity\Serie;
use netvod\entity\Episode;

class SerieRenderer
{
    public static function renderCard(Serie $serie): string
    {
        $id = htmlspecialchars($serie->id ?? '');
        $titre = htmlspecialchars($serie->titre ?? 'Sans titre');
        $descriptif = htmlspecialchars($serie->descriptif ?? 'Pas de description disponible');
        $annee = htmlspecialchars($serie->annee ?? 'N/A');
        $genre = htmlspecialchars($serie->genre ?? 'Non spécifié');
        
        if (strlen($descriptif) > 150) {
            $descriptif = substr($descriptif, 0, 150) . '...';
        }

        return <<<HTML
        <div class='card' onclick="window.location.href='index.php?action=serie&id={$id}'" style='cursor:pointer;'>
            <a href='index.php?action=serie&id={$id}'>
                <h3>{$titre}</h3>
            </a>
            <p><small>Année : {$annee}</small></p>
            <p><small>Genre : {$genre}</small></p>
            <p>{$descriptif}</p>
            <a href='index.php?action=serie&id={$id}'>Voir les épisodes →</a>
        </div>
HTML;
    }

    public static function renderDetail(Serie $serie): string
    {
        $titre = htmlspecialchars($serie->titre ?? 'Sans titre');
        $descriptif = htmlspecialchars($serie->descriptif ?? 'Pas de description disponible');
        $annee = htmlspecialchars($serie->annee ?? 'N/A');
        $genre = htmlspecialchars($serie->genre ?? 'Non spécifié');
        $dateSortie = htmlspecialchars($serie->date_sortie ?? 'N/A');
        $nbEpisodes = htmlspecialchars($serie->nb_episodes ?? 0);

        return <<<HTML
        <div class='serie-detail'>
            <h1>{$titre}</h1>
            <div style='margin-bottom: 20px;'>
                <small style='margin-right: 20px;'>Année : {$annee}</small>
                <small style='margin-right: 20px;'>Genre : {$genre}</small>
                <small style='margin-right: 20px;'>{$nbEpisodes} épisode(s)</small>
                <small>Sortie : {$dateSortie}</small>
            </div>
            <p style='line-height: 1.8; margin-bottom: 30px;'>{$descriptif}</p>
            <hr>
        </div>
HTML;
    }

    public static function renderWithEpisodes(Serie $serie, array $episodes): string
    {
        $html = self::renderDetail($serie);
        $html .= "<h2>Épisodes</h2>";
        $html .= EpisodeRenderer::renderList($episodes);
        
        return $html;
    }
}
