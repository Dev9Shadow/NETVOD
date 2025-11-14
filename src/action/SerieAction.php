<?php
namespace netvod\action;

use netvod\repository\ConnectionFactory;
use netvod\repository\SerieRepository;
use netvod\repository\EpisodeRepository;
use netvod\repository\CommentRepository;
use netvod\repository\ProgressRepository;
 

class SerieAction
{
    public string $title = '';
    public function execute(): string
    {
        ConnectionFactory::setConfig(__DIR__ . '/../../config/db.config.ini');

        $id = $_GET['id'] ?? 0;

        $serieRepo = new SerieRepository();
        $serie = $serieRepo->findById((int)$id);

        if (!$serie) {
            $this->title = "Erreur - NETVOD";
            return "<h1>Série non trouvée</h1><p><a href='index.php?action=catalogue'>Retour au catalogue</a></p>";
        }

        // Récupérer les épisodes
        $episodeRepo = new EpisodeRepository();
        $episodes = $episodeRepo->findBySerie($serie->id);
        $nbEpisodes = count($episodes);

        // Récupérer les statistiques de commentaires
        $commentRepo = new CommentRepository();
        $averageNote = $commentRepo->getAverageNote($serie->id);
        $nbComments = $commentRepo->countComments($serie->id);

        // Préparer les données
        $titre = htmlspecialchars($serie->titre);
        $descriptif = $serie->descriptif ?? 'Pas de description disponible';
        $genre = $serie->genre ?? 'Non spécifié';
        $publicVise = $serie->publicCible ? htmlspecialchars($serie->publicCible->nom) : 'Non spécifié';
        $annee = $serie->annee ?? 'N/A';
        $dateAjout = $serie->date_ajout ? date('d/m/Y', strtotime($serie->date_ajout)) : 'N/A';
        $imgName = $serie->img ?? 'default.jpg';
        $imgPath = 'images/' . $imgName;

        // Afficher la note moyenne
        $noteHtml = '';
        if ($averageNote !== null) {
            $noteHtml = "<div class='stat-item'>
                            <strong>Note moyenne</strong>
                            <span>{$averageNote}/5 ({$nbComments} avis)</span>
                         </div>";
        }

        // Bouton "Reprendre" (si connecté et progression trouvée)
        $reprendreBtn = '';
        if (session_status() === PHP_SESSION_NONE) session_start();
        if (isset($_SESSION['user_id'])) {
            $pr = new ProgressRepository();
            $last = $pr->get((int)$_SESSION['user_id'], (int)$serie->id);
            if ($last) {
                $reprendreBtn = "<div style='margin:12px 0 18px'>
                                   <a href='index.php?action=episode&id={$last}' class='btn btn-reprendre'>▶ Reprendre</a>
                                 </div>";
            }
        }

        // Construire le HTML
        $html = "<div class='serie-detail-header'>
                    <div class='serie-detail-poster'>
                        <img src='{$imgPath}' alt='{$titre}' onerror=\"this.src='images/default.jpg'\">
                    </div>
                    <div class='serie-detail-info'>
                        <h1>{$titre}</h1>

                        <div class='serie-meta'>
                            <span class='badge'>{$genre}</span>
                            <span class='badge'>{$publicVise}</span>
                            <span class='badge'>{$annee}</span>
                            <span class='badge'>{$nbEpisodes} épisode" . ($nbEpisodes > 1 ? 's' : '') . "</span>
                        </div>

                        {$reprendreBtn}

                        <div class='serie-description'>
                            <h3>Synopsis</h3>
                            <p>" . nl2br(htmlspecialchars($descriptif)) . "</p>
                        </div>

                        <div class='serie-stats'>
                            <div class='stat-item'>
                                <strong>Année de sortie</strong>
                                <span>{$annee}</span>
                            </div>
                            <div class='stat-item'>
                                <strong>Genre</strong>
                                <span>{$genre}</span>
                            </div>
                            <div class='stat-item'>
                                <strong>Public visé</strong>
                                <span>{$publicVise}</span>
                            </div>
                            <div class='stat-item'>
                                <strong>Ajoutée le</strong>
                                <span>{$dateAjout}</span>
                            </div>
                            {$noteHtml}
                        </div>

                        <div style='margin-top: 20px;'>
                            <a href='index.php?action=comments&id={$serie->id}' class='btn'>Voir les commentaires ({$nbComments})</a>
                        </div>
                    </div>
                 </div>";

        // Liste des épisodes
        $html .= "<div class='episodes-section'>
                    <h2>Épisodes <span class='episodes-count'>({$nbEpisodes})</span></h2>";

        if (empty($episodes)) {
            $html .= "<p class='no-episodes'>Aucun épisode disponible pour cette série.</p>";
        } else {
            $html .= "<div class='episode-list'>";
            foreach ($episodes as $ep) {
                $titre = htmlspecialchars($ep->titre ?? 'Sans titre');
                $resume = htmlspecialchars($ep->resume ?? 'Pas de résumé');
                $duree = $ep->duree ?? 0;
                $numero = $ep->numero ?? '?';

                $html .= "<div class='episode-item'>
                            <div class='episode-number'>{$numero}</div>
                            <div class='episode-content'>
                                <h4>{$titre}</h4>
                                <p class='episode-resume'>{$resume}</p>
                                <small class='episode-duration'>{$duree} min</small>
                            </div>
                            <div class='episode-actions'>
                                <a href='index.php?action=episode&id={$ep->id}' class='btn btn-play'>Lire</a>
                            </div>
                          </div>";
            }
            $html .= "</div>";
        }

        $html .= "</div>";

        $html .= "<p style='margin-top: 30px;'><a href='index.php?action=catalogue' class='btn btn-secondary'>Retour au catalogue</a></p>";

        $this->title = $titre . " - NETVOD";
        return $html;
    }
}
