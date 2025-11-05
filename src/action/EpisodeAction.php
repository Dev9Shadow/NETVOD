<?php
namespace netvod\action;

use netvod\repository\ConnectionFactory;
use netvod\repository\EpisodeRepository;
use netvod\renderer\EpisodeRenderer;
use netvod\renderer\Layout;

class EpisodeAction
{
    public function execute(): string
    {
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
        if ($ep->video_url && !str_starts_with($ep->video_url, 'videos/')) {
            $ep->video_url = 'videos/' . $ep->video_url;
        }

        $content = EpisodeRenderer::renderDetail($ep);
        
        return Layout::render($content, $ep->titre . " - NETVOD");
    }
}