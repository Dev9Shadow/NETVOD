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
}
