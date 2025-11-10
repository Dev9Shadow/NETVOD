<?php
namespace netvod\action;

use netvod\repository\ConnectionFactory;
use netvod\repository\SerieRepository;
use netvod\repository\CommentRepository;
use netvod\renderer\Layout;

class CommentsAction
{
    public function execute(): string
    {
        ConnectionFactory::setConfig(__DIR__ . '/../../config/db.config.ini');
        
        $id = $_GET['id'] ?? 0;
        
        $serieRepo = new SerieRepository();
        $serie = $serieRepo->findById((int)$id);
        
        if (!$serie) {
            return Layout::render(
                "<h1>Série non trouvée</h1><p><a href='index.php?action=catalogue'>Retour au catalogue</a></p>",
                "Erreur - NETVOD"
            );
        }
        
        $commentRepo = new CommentRepository();
        $comments = $commentRepo->findBySerie($serie->id);
        $averageNote = $commentRepo->getAverageNote($serie->id);
        
        $titre = htmlspecialchars($serie->titre);
        
        $html = "<h1>Commentaires - {$titre}</h1>";
        $html .= "<p><a href='index.php?action=serie&id={$serie->id}'>Retour à la série</a></p>";
        
        if ($averageNote !== null) {
            $html .= "<div class='card' style='background: #1a1a1a; margin-bottom: 30px;'>
                        <h2>Note moyenne : {$averageNote}/5</h2>
                        <p>" . count($comments) . " avis</p>
                      </div>";
        }
        
        if (empty($comments)) {
            $html .= "<p>Aucun commentaire pour cette série.</p>";
        } else {
            foreach ($comments as $comment) {
                $nom = htmlspecialchars($comment->user_prenom . ' ' . $comment->user_nom);
                $note = htmlspecialchars((string)$comment->note);
                $contenu = nl2br(htmlspecialchars($comment->contenu));
                $date = date('d/m/Y à H:i', strtotime($comment->created_at));
                
                $html .= "<div class='card' style='margin-bottom: 20px;'>
                            <div style='display: flex; justify-content: space-between; align-items: center; margin-bottom: 10px;'>
                                <strong>{$nom}</strong>
                                <span style='background: #e50914; padding: 5px 10px; border-radius: 5px;'>{$note}/5</span>
                            </div>
                            <p>{$contenu}</p>
                            <small style='color: #808080;'>{$date}</small>
                          </div>";
            }
        }
        
        return Layout::render($html, "Commentaires - {$titre} - NETVOD");
    }
}