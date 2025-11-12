<?php
declare(strict_types=1);

namespace netvod\repository;

use PDO;

class AlreadyWatchedRepository
{
    private function pdo(): PDO {
        if (method_exists(ConnectionFactory::class, 'getConnection')) {
            return ConnectionFactory::getConnection();
        }
        return ConnectionFactory::makeConnection();
    }

    /** Vrai si l'utilisateur a fini tous les épisodes de la série */
    public function isComplete(int $userId, int $serieId): bool
    {
        $pdo = $this->pdo();

        $q1 = $pdo->prepare('SELECT COUNT(*) FROM episode WHERE id_serie = :s');
        $q1->execute([':s' => $serieId]);
        $total = (int) $q1->fetchColumn();

        if ($total === 0) return false;

        $q2 = $pdo->prepare(
            'SELECT COUNT(*) 
               FROM episode_vue ev 
               JOIN episode e ON e.id = ev.id_episode
              WHERE ev.id_user = :u AND ev.vu = 1 AND e.id_serie = :s'
        );
        $q2->execute([':u' => $userId, ':s' => $serieId]);
        $seen = (int) $q2->fetchColumn();

        return $seen >= $total;
    }

    /** Inscrit la série en “déjà visionnées” si pas déjà */
    public function mark(int $userId, int $serieId): void
    {
        $pdo = $this->pdo();
        $st = $pdo->prepare(
            'INSERT IGNORE INTO already_watched (id_user, id_serie, marked_at) 
             VALUES (:u, :s, CURRENT_TIMESTAMP)'
        );
        $st->execute([':u' => $userId, ':s' => $serieId]);
    }

    /** Liste des séries déjà visionnées pour l’utilisateur (retourne des lignes de la table serie) */
    public function listForUser(int $userId): array
    {
        $pdo = $this->pdo();
        $st = $pdo->prepare(
            'SELECT s.*
               FROM already_watched aw
               JOIN serie s ON s.id = aw.id_serie
              WHERE aw.id_user = :u
              ORDER BY aw.marked_at DESC'
        );
        $st->execute([':u' => $userId]);
        return $st->fetchAll(PDO::FETCH_ASSOC);
    }

    /** (optionnel) Nettoie progress quand la série est finie */
    public function clearProgress(int $userId, int $serieId): void
    {
        $pdo = $this->pdo();
        $pdo->prepare('DELETE FROM progress WHERE id_user = :u AND id_serie = :s')
            ->execute([':u' => $userId, ':s' => $serieId]);
    }
}
