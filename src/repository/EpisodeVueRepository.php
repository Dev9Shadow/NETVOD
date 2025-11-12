<?php
declare(strict_types=1);

namespace netvod\repository;

use PDO;

class EpisodeVueRepository
{
    private function pdo(): PDO {
        if (method_exists(ConnectionFactory::class, 'getConnection')) return ConnectionFactory::getConnection();
        return ConnectionFactory::makeConnection();
    }

    public function upsert(int $idUser, int $idEpisode, int $positionSec, int $vu): void {
        $pdo = $this->pdo();
        $sql = "INSERT INTO episode_vue (id_user, id_episode, position_sec, vu, updated_at)
                VALUES (:u,:e,:p,:v,CURRENT_TIMESTAMP)
                ON DUPLICATE KEY UPDATE
                  position_sec=:p2,
                  vu=GREATEST(vu,:v2),
                  updated_at=CURRENT_TIMESTAMP";
        $st = $pdo->prepare($sql);
        $st->execute([
            ':u'=>$idUser, ':e'=>$idEpisode, ':p'=>$positionSec, ':v'=>$vu,
            ':p2'=>$positionSec, ':v2'=>$vu
        ]);
    }

    public function get(int $idUser, int $idEpisode): ?array {
        $pdo = $this->pdo();
        $st = $pdo->prepare("SELECT id_user,id_episode,position_sec,vu FROM episode_vue WHERE id_user=:u AND id_episode=:e");
        $st->execute([':u'=>$idUser, ':e'=>$idEpisode]);
        $row = $st->fetch(PDO::FETCH_ASSOC);
        return $row ?: null;
    }
}
