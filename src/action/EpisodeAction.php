<?php
namespace netvod\action;

use netvod\repository\ConnectionFactory;
use netvod\repository\EpisodeRepository;

class EpisodeAction
{
    public function execute(): string
    {
        ConnectionFactory::setConfig(__DIR__ . '/../../config/db.config.ini');

        $id = $_GET['id'] ?? null;
        if ($id === null) return "<p>Aucun épisode spécifié.</p>";

        $repo = new EpisodeRepository();
        $ep = $repo->findById((int)$id);
        if (!$ep) return "<p>Épisode introuvable.</p>";

        // le chemin du fichier vidéo (tu peux le mettre dans un dossier public/videos/)
        $videoPath = "videos/" . htmlspecialchars($ep->file);

        $html = "<h1>{$ep->titre}</h1>
                 <p><strong>Durée :</strong> {$ep->duree} minutes</p>
                 <p>{$ep->resume}</p>
                 <video width='640' controls>
                     <source src='{$videoPath}' type='video/mp4'>
                     Votre navigateur ne supporte pas la lecture vidéo.
                 </video>
                 <p><a href='index.php?action=serie&id={$ep->serie_id}'>⬅ Retour à la série</a></p>";

        return $html;
    }
}
