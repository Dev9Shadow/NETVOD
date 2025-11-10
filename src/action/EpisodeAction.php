<?php
namespace netvod\action;

use netvod\repository\ConnectionFactory;
use netvod\repository\EpisodeRepository;
use netvod\repository\CommentRepository;
use netvod\entity\Comment;
use netvod\renderer\EpisodeRenderer;
use netvod\renderer\Layout;

class EpisodeAction
{
    public function execute(): string
    {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }

        ConnectionFactory::setConfig(__DIR__ . '/../../config/db.config.ini');

        $id = $_GET['id'] ?? null;
        
        if ($id === null) {
            $content = EpisodeRenderer::renderNotFound();
            return Layout::render($content, "Épisode - NETVOD");
        }

        $repo = new EpisodeRepository();
        $ep = $repo->findById((int)$id);
        
        if (!$ep) {
            $content = EpisodeRenderer::renderNotFound();
            return Layout::render($content, "Épisode - NETVOD");
        }

        // Ajouter le chemin complet à la vidéo
        if ($ep->file && !str_starts_with($ep->file, 'videos/')) {
            $ep->file = 'videos/' . $ep->file;
        }

        // Gérer la soumission du commentaire
        $message = '';
        if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_SESSION['user_id'])) {
            $note = (int)($_POST['note'] ?? 0);
            $contenu = trim($_POST['contenu'] ?? '');
            
            if ($note >= 1 && $note <= 5 && $contenu !== '') {
                $comment = new Comment();
                $comment->id_user = $_SESSION['user_id'];
                $comment->id_serie = $ep->id_serie;
                $comment->note = $note;
                $comment->contenu = $contenu;
                
                $commentRepo = new CommentRepository();
                if ($commentRepo->save($comment)) {
                    $message = "<p class='success'>Votre commentaire a été enregistré.</p>";
                } else {
                    $message = "<p class='error'>Erreur lors de l'enregistrement du commentaire.</p>";
                }
            } else {
                $message = "<p class='error'>Veuillez remplir tous les champs correctement.</p>";
            }
        }

        // Récupérer le commentaire existant de l'utilisateur
        $userComment = null;
        if (isset($_SESSION['user_id'])) {
            $commentRepo = new CommentRepository();
            $userComment = $commentRepo->findByUserAndSerie($_SESSION['user_id'], $ep->id_serie);
        }

        $content = EpisodeRenderer::renderDetail($ep, $userComment, $message);
        
        return Layout::render($content, $ep->titre . " - NETVOD");
    }
}