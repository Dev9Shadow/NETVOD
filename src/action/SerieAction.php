<?php
declare(strict_types=1);

namespace netvod\action;

use netvod\repository\ConnectionFactory;
use netvod\repository\SerieRepository;
use netvod\repository\EpisodeRepository;
use netvod\repository\ProgressRepository;
use netvod\renderer\Layout;

class SerieAction
{
    public function execute(): string
    {
        ConnectionFactory::setConfig(__DIR__ . '/../../config/db.config.ini');
        if (session_status() === PHP_SESSION_NONE) session_start();

        $id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
        if ($id <= 0) {
            return Layout::render(
                "<h1>Série non trouvée</h1><p><a href='index.php?action=catalogue'>⬅ Retour au catalogue</a></p>",
                "Erreur - NETVOD"
            );
        }

        $serieRepo = new SerieRepository();
        $serie     = $serieRepo->findById($id);
        if (!$serie) {
            return Layout::render(
                "<h1>Série non trouvée</h1><p><a href='index.php?action=catalogue'>⬅ Retour au catalogue</a></p>",
                "Erreur - NETVOD"
            );
        }

        // Episodes de la série
        $episodeRepo = new EpisodeRepository();
        $episodes    = $episodeRepo->findBySerie($serie->id);
        $nbEpisodes  = count($episodes);

        // Champs (avec fallback simple)
        $titre      = htmlspecialchars($serie->titre ?? 'Sans titre');
        $descriptif = $serie->descriptif ?? 'Pas de description disponible';
        $genre      = $serie->genre ?? 'Non spécifié';
        $annee      = $serie->annee ?? 'N/A';
        $dateAjout  = $serie->date_ajout ? date('d/m/Y', strtotime($serie->date_ajout)) : 'N/A';
        $imgName    = $serie->img ?? 'default.jpg';
        $imgPath    = 'images/' . $imgName;

        // --------- Bouton "Reprendre" (si user connecté + progress existant) ----------
        $reprendreBtn = '';
        if (isset($_SESSION['user_id'])) {
            $pr    = new ProgressRepository();
            $last  = $pr->get((int)$_SESSION['user_id'], (int)$serie->id); // last_episode_id ou null
            if (!empty($last)) {
                $lastId = (int)$last;
                $reprendreBtn = "<p style='margin: 12px 0 0'>
                    <a class='btn' href='index.php?action=episode&id={$lastId}'>▶ Reprendre</a>
                </p>";
            }
        }

        // --------- HTML ----------
        $html = "
        <div class='serie-detail-header'>
            <div class='serie-detail-poster'>
                <img src='{$imgPath}' alt='{$titre}' onerror=\"this.src='images/default.jpg'\">
            </div>
            <div class='serie-detail-info'>
                <h1>{$titre}</h1>

                <div class='serie-meta'>
                    <span class='badge'>" . htmlspecialchars($genre) . "</span>
                    <span class='badge'>" . htmlspecialchars((string)$annee) . "</span>
                    <span class='badge'>{$nbEpisodes} épisode" . ($nbEpisodes > 1 ? 's' : '') . "</span>
                </div>

                <div class='serie-description'>
                    <h3>Synopsis</h3>
                    <p>" . nl2br(htmlspecialchars($descriptif)) . "</p>
                </div>

                {$reprendreBtn}

                <div class='serie-stats' style='margin-top:16px'>
                    <div class='stat-item'><strong>Année de sortie</strong><span>{$annee}</span></div>
                    <div class='stat-item'><strong>Ajoutée le</strong><span>{$dateAjout}</span></div>
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
                $etitre = htmlspecialchars($ep->titre ?? 'Sans titre');
                $resume = htmlspecialchars($ep->resume ?? 'Pas de résumé');
                $duree  = (int)($ep->duree ?? 0);
                $numero = htmlspecialchars((string)($ep->numero ?? '?'));
                $eid    = (int)($ep->id ?? 0);

                $html .= "<div class='episode-item'>
                            <div class='episode-number'>{$numero}</div>
                            <div class='episode-content'>
                                <h4>{$etitre}</h4>
                                <p class='episode-resume'>{$resume}</p>
                                <small class='episode-duration'>{$duree} min</small>
                            </div>
                            <div class='episode-actions'>
                                <a href='index.php?action=episode&id={$eid}' class='btn btn-play'>▶ Lire</a>
                            </div>
                          </div>";
            }
            $html .= "</div>";
        }

        $html .= "</div>
                  <p style='margin-top: 30px;'>
                    <a href='index.php?action=catalogue' class='btn btn-secondary'>⬅ Retour au catalogue</a>
                  </p>";

        return Layout::render($html, $titre . " - NETVOD");
    }
}
