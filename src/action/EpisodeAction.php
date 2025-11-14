<?php
declare(strict_types=1);

namespace netvod\action;

use netvod\repository\ConnectionFactory;
use netvod\repository\EpisodeRepository;
use netvod\repository\EpisodeVueRepository;
use netvod\repository\ProgressRepository;
use netvod\repository\CommentRepository;
use netvod\renderer\EpisodeRenderer;

class EpisodeAction
{
    public string $title = '';
    private function pdo()
    {
        // Compat : certaines bases ont getConnection(), d’autres getConnection()
        
        return ConnectionFactory::getConnection();
    }

    public function execute(): string
    {
        ConnectionFactory::setConfig(__DIR__ . '/../../config/db.config.ini');
        if (session_status() === PHP_SESSION_NONE) session_start();

        /* A) POST : ajout commentaire (form sur la page épisode) */
        if ($_SERVER['REQUEST_METHOD'] === 'POST'
            && isset($_POST['do']) && $_POST['do'] === 'add_comment') {

            if (!isset($_SESSION['user_id'])) {
                $_SESSION['flash_error'] = "Vous devez être connecté pour commenter.";
                header('Location: index.php?action=login');
                exit;
            }

            $idSerie = (int)($_POST['id_serie'] ?? 0);
            $note    = (int)($_POST['note'] ?? 0);
            $contenu = trim((string)($_POST['contenu'] ?? ''));
            $idEpUrl = (int)($_GET['id'] ?? 0);

            if ($idSerie > 0 && $note >= 1 && $note <= 5 && $contenu !== '') {
                $cr = new CommentRepository();
                $cr->add((int)$_SESSION['user_id'], $idSerie, $note, $contenu);
                $_SESSION['flash_success'] = "Votre avis a été publié.";
            } else {
                $_SESSION['flash_error'] = "Champs invalides.";
            }

            header('Location: index.php?action=episode&id=' . $idEpUrl);
            exit;
        }

        /* B) POST AJAX : autosave position + “vu” + gestion déjà visionnée */
        if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_SESSION['user_id']) && isset($_POST['episode_id'])) {
            $idUser = (int) $_SESSION['user_id'];
            $idEp   = (int) ($_POST['episode_id'] ?? 0);
            $pos    = max(0, (int) ($_POST['position_sec'] ?? 0));
            $vu     = isset($_POST['vu']) ? 1 : 0;

            if ($idEp > 0) {
                $vueRepo = new EpisodeVueRepository();
                $vueRepo->upsert($idUser, $idEp, $pos, $vu);

                // Si l’épisode est terminé -> MAJ "reprendre" et peut-être "déjà visionnées"
                if ($vu === 1) {
                    $epRepo  = new EpisodeRepository();
                    $episode = $epRepo->findById($idEp);

                    if ($episode) {
                        // 1) Reprendre : mémoriser dernier épisode
                        $progRepo = new ProgressRepository();
                        $progRepo->upsert($idUser, (int)$episode->id_serie, $idEp);

                        // 2) Déjà visionnées : si tous les épisodes de la série sont vus
                        $pdo = $this->pdo();
                        $idSerie = (int)$episode->id_serie;

                        // nb total d'épisodes de la série
                        $st = $pdo->prepare("SELECT COUNT(*) FROM episode WHERE id_serie = :s");
                        $st->execute([':s' => $idSerie]);
                        $total = (int) $st->fetchColumn();

                        // nb d'épisodes vus (vu=1) par l'utilisateur pour cette série
                        $sqlVu = "SELECT COUNT(*) 
                                  FROM episode_vue ev 
                                  JOIN episode e ON e.id = ev.id_episode
                                  WHERE ev.id_user = :u AND ev.vu = 1 AND e.id_serie = :s";
                        $st2 = $pdo->prepare($sqlVu);
                        $st2->execute([':u' => $idUser, ':s' => $idSerie]);
                        $vus = (int) $st2->fetchColumn();

                        if ($total > 0 && $vus >= $total) {
                            // marquer dans already_watched (idempotent)
                            $pdo->prepare(
                                "INSERT IGNORE INTO already_watched (id_user, id_serie, marked_at)
                                 VALUES (:u, :s, NOW())"
                            )->execute([':u' => $idUser, ':s' => $idSerie]);

                            // optionnel : retirer la série de progress
                            $pdo->prepare(
                                "DELETE FROM progress WHERE id_user = :u AND id_serie = :s"
                            )->execute([':u' => $idUser, ':s' => $idSerie]);
                        }
                    }
                }
            }

            header('Content-Type: application/json');
            echo json_encode(['ok' => true]);
            exit;
        }

        /* C) GET : afficher l’épisode + reprise auto */
        $id = isset($_GET['id']) ? (int) $_GET['id'] : 0;
        if ($id <= 0) {
            $content = EpisodeRenderer::renderNotFound();
            $this->title = "Épisode - NETVOD";
            return $content;
        }

        $repo = new EpisodeRepository();
        $ep   = $repo->findById($id);
        if (!$ep) {
            $content = EpisodeRenderer::renderNotFound();
            $this->title = "Épisode - NETVOD";
            return $content;
        }

        // Préfix "videos/" si besoin
        if (!empty($ep->file) && substr((string)$ep->file, 0, 7) !== 'videos/') {
            $ep->file = 'videos/' . $ep->file;
        }

        // Reprise à la dernière position
        $resumeFrom = 0;
        $logged     = isset($_SESSION['user_id']);
        if ($logged) {
            $evr = new EpisodeVueRepository();
            $row = $evr->get((int)$_SESSION['user_id'], (int)$ep->id);
            if ($row) $resumeFrom = (int)$row['position_sec'];
        }

        $content = EpisodeRenderer::renderDetail($ep, $resumeFrom, $logged);
        $this->title = ($ep->titre ?? 'Épisode') . " - NETVOD";
        return $content;
    }
}
