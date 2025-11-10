<?php
namespace netvod\renderer;

use netvod\entity\Episode;
use netvod\entity\Comment;

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
            <h4>Episode {$numero} : {$titre}</h4>
            <p><small>Durée : {$duree} min</small></p>
            <p>{$resume}</p>
            <a href='index.php?action=episode&id={$id}'>Regarder</a>
        </div>
HTML;
    }

    /**
     * Affiche les détails complets d'un épisode avec lecteur vidéo et formulaire de commentaire
     * @param Episode $episode
     * @param Comment|null $userComment
     * @param string $message
     * @return string
     */
    public static function renderDetail(Episode $episode, ?Comment $userComment = null, string $message = ''): string
    {
        $titre = htmlspecialchars($episode->titre ?? 'Sans titre');
        $numero = htmlspecialchars((string)($episode->numero ?? 'N/A'));
        $duree = htmlspecialchars((string)($episode->duree ?? 0));
        $resume = htmlspecialchars($episode->resume ?? 'Pas de résumé disponible');
        $videoPath = $episode->file ?? '';
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
                Retour à la série
            </a>";
        }

        // Formulaire de commentaire
        $commentForm = '';
        if (isset($_SESSION['user_id'])) {
            $noteValue = $userComment ? $userComment->note : 3;
            $contenuValue = $userComment ? htmlspecialchars($userComment->contenu) : '';
            $buttonText = $userComment ? 'Modifier mon commentaire' : 'Envoyer mon commentaire';
            
            $commentForm = <<<HTML
            <div class='card' style='margin-top: 30px;'>
                <h2>Noter et commenter cette série</h2>
                {$message}
                <form method='POST'>
                    <div>
                        <label>Note (1 à 5) :</label>
                        <select name='note' required>
                            <option value='1' " . ($noteValue == 1 ? 'selected' : '') . ">1 - Très mauvais</option>
                            <option value='2' " . ($noteValue == 2 ? 'selected' : '') . ">2 - Mauvais</option>
                            <option value='3' " . ($noteValue == 3 ? 'selected' : '') . ">3 - Moyen</option>
                            <option value='4' " . ($noteValue == 4 ? 'selected' : '') . ">4 - Bon</option>
                            <option value='5' " . ($noteValue == 5 ? 'selected' : '') . ">5 - Excellent</option>
                        </select>
                    </div>
                    <div>
                        <label>Commentaire :</label>
                        <textarea name='contenu' rows='5' required>{$contenuValue}</textarea>
                    </div>
                    <button type='submit'>{$buttonText}</button>
                </form>
            </div>
HTML;
        } else {
            $commentForm = <<<HTML
            <div class='card' style='margin-top: 30px;'>
                <p>Vous devez être connecté pour noter et commenter cette série.</p>
                <a href='index.php?action=login' class='btn'>Se connecter</a>
            </div>
HTML;
        }

        return <<<HTML
        <div class='episode-detail'>
            {$backLink}
            <h1>Episode {$numero} : {$titre}</h1>
            <p><small>Durée : {$duree} minutes</small></p>
            
            {$videoHtml}
            
            <h2>Résumé</h2>
            <p style='line-height: 1.8;'>{$resume}</p>
            
            {$commentForm}
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
            <h2>Episode introuvable</h2>
            <p>L'épisode que vous recherchez n'existe pas ou a été supprimé.</p>
            <a href='index.php?action=catalogue'>Retour au catalogue</a>
        </div>
HTML;
    }
}