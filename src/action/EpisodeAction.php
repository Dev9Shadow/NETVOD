<?php
declare(strict_types=1);

namespace netvod\action;

use netvod\repository\ConnectionFactory;
use netvod\repository\EpisodeRepository;
use netvod\repository\EpisodeVueRepository;
use netvod\repository\ProgressRepository;
use netvod\repository\CommentRepository;
use netvod\renderer\EpisodeRenderer;
use netvod\renderer\Layout;

class EpisodeAction
{
    public function execute(): string
    {
        ConnectionFactory::setConfig(__DIR__ . '/../../config/db.config.ini');
        if (session_status() === PHP_SESSION_NONE) session_start();

        /* A) POST : ajout d’un commentaire (depuis la page épisode) */
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

        /* B) POST AJAX : sauvegarde position / marquer vu (reprendre auto) */
        if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_SESSION['user_id']) && isset($_POST['episode_id'])) {
            $idUser = (int) $_SESSION['user_id'];
            $idEp   = (int) ($_POST['episode_id'] ?? 0);
            $pos    = max(0, (int) ($_POST['position_sec'] ?? 0));
            $vu     = isset($_POST['vu']) ? 1 : 0;

            if ($idEp > 0) {
                $vueRepo = new EpisodeVueRepository();
                $vueRepo->upsert($idUser, $idEp, $pos, $vu);

                if ($vu === 1) {
                    $epRepo  = new EpisodeRepository();
                    $episode = $epRepo->findById($idEp);
                    if ($episode) {
                        $progRepo = new ProgressRepository();
                        $progRepo->upsert($idUser, (int)$episode->id_serie, $idEp);
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
            return Layout::render($content, "Épisode - NETVOD");
        }

        $repo = new EpisodeRepository();
        $ep   = $repo->findById($id);
        if (!$ep) {
            $content = EpisodeRenderer::renderNotFound();
            return Layout::render($content, "Épisode - NETVOD");
        }

        if (!empty($ep->file) && substr((string)$ep->file, 0, 7) !== 'videos/') {
            $ep->file = 'videos/' . $ep->file;
        }

        $resumeFrom = 0;
        $logged     = isset($_SESSION['user_id']);
        if ($logged) {
            $evr = new EpisodeVueRepository();
            $row = $evr->get((int)$_SESSION['user_id'], (int)$ep->id);
            if ($row) $resumeFrom = (int)$row['position_sec'];
        }

        $content = EpisodeRenderer::renderDetail($ep, $resumeFrom, $logged);
        return Layout::render($content, ($ep->titre ?? 'Épisode') . " - NETVOD");
    }
}
