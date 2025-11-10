<?php
namespace netvod\repository;

use netvod\entity\Comment;
use PDO;

class CommentRepository
{
    public function findByUserAndSerie(int $userId, int $serieId): ?Comment
    {
        $pdo = ConnectionFactory::getConnection();
        $stmt = $pdo->prepare("SELECT * FROM comment WHERE id_user = ? AND id_serie = ?");
        $stmt->execute([$userId, $serieId]);
        
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        if (!$row) {
            return null;
        }
        
        $comment = new Comment();
        $comment->id = (int)$row['id'];
        $comment->id_user = (int)$row['id_user'];
        $comment->id_serie = (int)$row['id_serie'];
        $comment->note = (int)$row['note'];
        $comment->contenu = $row['contenu'];
        $comment->created_at = $row['created_at'];
        
        return $comment;
    }

    public function findBySerie(int $serieId): array
    {
        $pdo = ConnectionFactory::getConnection();
        $query = "SELECT c.*, u.nom as user_nom, u.prenom as user_prenom 
                  FROM comment c 
                  LEFT JOIN user u ON c.id_user = u.id 
                  WHERE c.id_serie = ? 
                  ORDER BY c.created_at DESC";
        $stmt = $pdo->prepare($query);
        $stmt->execute([$serieId]);
        
        $comments = [];
        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $comment = new Comment();
            $comment->id = (int)$row['id'];
            $comment->id_user = (int)$row['id_user'];
            $comment->id_serie = (int)$row['id_serie'];
            $comment->note = (int)$row['note'];
            $comment->contenu = $row['contenu'];
            $comment->created_at = $row['created_at'];
            $comment->user_nom = $row['user_nom'] ?? '';
            $comment->user_prenom = $row['user_prenom'] ?? '';
            
            $comments[] = $comment;
        }
        
        return $comments;
    }

    public function save(Comment $comment): bool
    {
        $pdo = ConnectionFactory::getConnection();
        
        // Vérifier si un commentaire existe déjà
        $existing = $this->findByUserAndSerie($comment->id_user, $comment->id_serie);
        
        if ($existing) {
            // Mise à jour
            $stmt = $pdo->prepare(
                "UPDATE comment SET note = ?, contenu = ? WHERE id_user = ? AND id_serie = ?"
            );
            return $stmt->execute([
                $comment->note,
                $comment->contenu,
                $comment->id_user,
                $comment->id_serie
            ]);
        } else {
            // Insertion
            $stmt = $pdo->prepare(
                "INSERT INTO comment (id_user, id_serie, note, contenu) VALUES (?, ?, ?, ?)"
            );
            return $stmt->execute([
                $comment->id_user,
                $comment->id_serie,
                $comment->note,
                $comment->contenu
            ]);
        }
    }

    public function getAverageNote(int $serieId): ?float
    {
        $pdo = ConnectionFactory::getConnection();
        $stmt = $pdo->prepare("SELECT AVG(note) as moyenne FROM comment WHERE id_serie = ?");
        $stmt->execute([$serieId]);
        
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        if ($row && $row['moyenne'] !== null) {
            return round((float)$row['moyenne'], 1);
        }
        
        return null;
    }

    public function countComments(int $serieId): int
    {
        $pdo = ConnectionFactory::getConnection();
        $stmt = $pdo->prepare("SELECT COUNT(*) as total FROM comment WHERE id_serie = ?");
        $stmt->execute([$serieId]);
        
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        return (int)($row['total'] ?? 0);
    }
}