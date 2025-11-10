<?php
declare(strict_types=1);

namespace netvod\repository;

use PDO;

class ProgressRepository
{
    private function pdo(): PDO {
        if (method_exists(ConnectionFactory::class, 'getConnection')) {
            return ConnectionFactory::getConnection();
        }
        return ConnectionFactory::makeConnection();
    }

    public function upsert(int $idUser, int $idSerie, int $lastEpisodeId): void
    {
        $pdo = $this->pdo();
        $sql = "INSERT INTO progress (id_user, id_serie, last_episode_id, updated_at)
                VALUES (:u, :s, :e, CURRENT_TIMESTAMP)
                ON DUPLICATE KEY UPDATE
                  last_episode_id = :e2,
                  updated_at = CURRENT_TIMESTAMP";
        $st = $pdo->prepare($sql);
        $st->execute([
            ':u'  => $idUser,
            ':s'  => $idSerie,
            ':e'  => $lastEpisodeId,
            ':e2' => $lastEpisodeId,
        ]);
    }

    public function get(int $idUser, int $idSerie): ?int
    {
        $pdo = $this->pdo();
        $st  = $pdo->prepare("SELECT last_episode_id FROM progress WHERE id_user = :u AND id_serie = :s");
        $st->execute([':u' => $idUser, ':s' => $idSerie]);
        $val = $st->fetchColumn();
        return $val !== false ? (int)$val : null;
    }

    /**
     * Liste des séries “en cours” pour un utilisateur, avec détails
     * - s.titre / s.img
     * - e.id / e.titre / e.numero
     * - position_sec depuis episode_vue (si dispo)
     * Triées par progress.updated_at DESC
     */
    public function listForUser(int $idUser): array
    {
        $pdo = $this->pdo();
        $sql = "SELECT
                    p.id_serie,
                    s.titre AS serie_titre,
                    s.img   AS serie_img,
                    p.last_episode_id AS ep_id,
                    e.titre AS ep_titre,
                    e.numero AS ep_numero,
                    COALESCE(ev.position_sec, 0) AS position_sec,
                    p.updated_at
                FROM progress p
                JOIN serie   s ON s.id = p.id_serie
                LEFT JOIN episode e ON e.id = p.last_episode_id
                LEFT JOIN episode_vue ev
                       ON ev.id_user = p.id_user
                      AND ev.id_episode = p.last_episode_id
                WHERE p.id_user = :u
                ORDER BY p.updated_at DESC";
        $st = $pdo->prepare($sql);
        $st->execute([':u' => $idUser]);
        return $st->fetchAll(PDO::FETCH_ASSOC) ?: [];
    }
}
