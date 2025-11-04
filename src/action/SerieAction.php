<?php
namespace netvod\action;

use netvod\repository\ConnectionFactory;
use netvod\repository\SerieRepository;
use netvod\repository\EpisodeRepository;

class SerieAction
{
    public function execute(): string
    {
        ConnectionFactory::setConfig(__DIR__ . '/../../config/db.config.ini');

        $id = $_GET['id'] ?? null;
        if ($id === null) return "<p>Aucune série spécifiée.</p>";

        $serieRepo = new SerieRepository();
        $episodeRepo = new EpisodeRepository();

        $serie = $serieRepo->findById((int)$id);
        if (!$serie) return "<p>Série introuvable.</p>";

        $episodes = $episodeRepo->findBySerie((int)$id);

        $html = "<h1>{$serie->titre}</h1>
                 <p>{$serie->descriptif}</p>
                 <p><strong>Année :</strong> {$serie->annee} | 
                    <strong>Ajoutée le :</strong> {$serie->date_ajout}</p>
                 <hr>
                 <h2>Épisodes</h2>";

        foreach ($episodes as $ep) {
            $html .= $ep;
        }

        $html .= "<p><a href='index.php?action=catalogue'>⬅ Retour au catalogue</a></p>";
        return $html;
    }
}
