<?php
declare(strict_types=1);

namespace netvod\repository;

use PDO;

class CommentRepository
{
    private function pdo(): PDO
    {
        if (method_exists(ConnectionFactory::class, 'getConnection')) {
            return ConnectionFactory::getConnection();
        }
        return ConnectionFactory::makeConnection();
    }

    /** Ajout d’un commentaire pour une série */
    public function add(int $idUser, int $idSerie, int $note, string $contenu): void
    {
        $pdo = $this->pdo();
        $sql = "INSERT INTO comment (id_user, id_serie, note, contenu, created_at)
                VALUES (:u, :s, :n, :c, NOW())";
        $st = $pdo->prepare($sql);
        $st->execute([
            ':u' => $idUser,
            ':s' => $idSerie,
            ':n' => $note,
            ':c' => $contenu,
        ]);
    }

    /** Liste des commentaires d’une série  */
    public function findBySerie(int $idSerie): array
    {
        $pdo = $this->pdo();
        $sql = "SELECT c.*, u.nom AS user_nom, u.prenom AS user_prenom
                FROM comment c
                JOIN user u ON u.id = c.id_user
                WHERE c.id_serie = :s
                ORDER BY c.created_at DESC";
        $st = $pdo->prepare($sql);
        $st->execute([':s' => $idSerie]);
        $rows = $st->fetchAll(PDO::FETCH_ASSOC);
        return array_map(static fn($r) => (object)$r, $rows);
    }

    /** Note moyenne sur une série */
    public function getAverageNote(int $idSerie): ?float
    {
        $pdo = $this->pdo();
        $st = $pdo->prepare("SELECT AVG(note) FROM comment WHERE id_serie = :s");
        $st->execute([':s' => $idSerie]);
        $val = $st->fetchColumn();
        return $val !== false ? round((float)$val, 1) : null;
    }

    /** Nombre de commentaires sur une série */
    public function countComments(int $idSerie): int
    {
        $pdo = $this->pdo();
        $st = $pdo->prepare("SELECT COUNT(*) FROM comment WHERE id_serie = :s");
        $st->execute([':s' => $idSerie]);
        return (int)$st->fetchColumn();
    }
}
