<?php
namespace netvod\renderer;

use netvod\entity\Episode;

class EpisodeRenderer
{
    /**
     * Affiche une liste d'épisodes
     * @param Episode[] $episodes
     * @return string
     */
    public static function renderList(array $episodes): string
    {
        if (empty($episodes)) {
            return "<p>Aucun épisode disponible pour cette série.</p>";
        }

        $html = "<div class='episode-list'>";
        
        foreach ($episodes as $episode) {
            $html .= self::renderItem($episode);
        }
        
        $html .= "</div>";
        
        return $html;
    }

    /**
     * Affiche un item d'épisode dans une liste
     * @param Episode $episode
     * @return string
     */
    public static function renderItem(Episode $episode): string
    {
        $id = htmlspecialchars((string)($episode->id ?? ''));
        $titre = htmlspecialchars($episode->titre ?? 'Sans titre');
        $numero = htmlspecialchars((string)($episode->numero ?? 'N/A'));
        $duree = htmlspecialchars((string)($episode->duree ?? 0));
        $resume = htmlspecialchars($episode->resume ?? 'Pas de résumé disponible');
        
        // Limiter le résumé à 200 caractères
        if (strlen($resume) > 200) {
            $resume = substr($resume, 0, 200) . '...';
        }

        return <<<HTML
        <div class='episode-item'>
            <h4>Épisode {$numero} : {$titre}</h4>
            <p><small>⏱️ Durée : {$duree} min</small></p>
            <p>{$resume}</p>
            <a href='index.php?action=episode&id={$id}'>Regarder →</a>
        </div>
HTML;
    }

    /**
     * Affiche les détails complets d'un épisode avec lecteur vidéo
     * @param Episode $episode
     * @return string
     */
    public static function renderDetail(Episode $episode): string
    {
        $titre = htmlspecialchars($episode->titre ?? 'Sans titre');
        $numero = htmlspecialchars((string)($episode->numero ?? 'N/A'));
        $duree = htmlspecialchars((string)($episode->duree ?? 0));
        $resume = htmlspecialchars($episode->resume ?? 'Pas de résumé disponible');
        $videoPath = $episode->video_url ?? '';
        $serieId = (string)($episode->id_serie ?? '');

        $videoHtml = '';
        if (!empty($videoPath)) {
            $videoPathSafe = htmlspecialchars($videoPath);
            $videoHtml = <<<HTML
            <div style='margin: 30px 0;'>
                <video controls width='100%' style='max-width: 800px; border-radius: 8px;'>
                    <source src='{$videoPathSafe}' type='video/mp4'>
                    Votre navigateur ne supporte pas la balise vidéo.
                </video>
            </div>
HTML;
        }

        $backLink = '';
        if (!empty($serieId)) {
            $backLink = "<a href='index.php?action=serie&id={$serieId}' style='display: inline-block; margin-bottom: 20px;'>
                ← Retour à la série
            </a>";
        }

        return <<<HTML
        <div class='episode-detail'>
            {$backLink}
            <h1>Épisode {$numero} : {$titre}</h1>
            <p><small>⏱️ Durée : {$duree} minutes</small></p>
            
            {$videoHtml}
            
            <h2>Résumé</h2>
            <p style='line-height: 1.8;'>{$resume}</p>
        </div>
HTML;
    }

    /**
     * Affiche un message si aucun épisode n'est trouvé
     * @return string
     */
    public static function renderNotFound(): string
    {
        return <<<HTML
        <div class='error'>
            <h2>Épisode introuvable</h2>
            <p>L'épisode que vous recherchez n'existe pas ou a été supprimé.</p>
            <a href='index.php?action=catalogue'>Retour au catalogue</a>
        </div>
HTML;
    }
}