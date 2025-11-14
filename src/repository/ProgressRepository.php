<?php
declare(strict_types=1);

namespace netvod\repository;

use PDO;

class ProgressRepository
{
    public function upsert(int $idUser, int $idSerie, int $lastEpisodeId): void {
        $pdo = ConnectionFactory::getConnection();
        $sql = "INSERT INTO progress (id_user,id_serie,last_episode_id,updated_at)
                VALUES (:u,:s,:e,CURRENT_TIMESTAMP)
                ON DUPLICATE KEY UPDATE last_episode_id=:e2, updated_at=CURRENT_TIMESTAMP";
        $st = $pdo->prepare($sql);
        $st->execute([':u'=>$idUser, ':s'=>$idSerie, ':e'=>$lastEpisodeId, ':e2'=>$lastEpisodeId]);
    }

    public function get(int $idUser, int $idSerie): ?int {
        $pdo = ConnectionFactory::getConnection();
        $st = $pdo->prepare("SELECT last_episode_id FROM progress WHERE id_user=:u AND id_serie=:s");
        $st->execute([':u'=>$idUser, ':s'=>$idSerie]);
        $v = $st->fetchColumn();
        return $v!==false ? (int)$v : null;
    }

    public function listForUser(int $idUser): array {
        $pdo = ConnectionFactory::getConnection();
        $sql = "SELECT
                  ev.id_episode AS ep_id,
                  ev.position_sec,
                  ev.vu,
                  ev.updated_at as ev_updated,
                  e.id_serie,
                  e.titre      AS ep_titre,
                  e.numero     AS ep_numero,
                  s.titre      AS serie_titre,
                  s.img        AS serie_img
                FROM episode_vue ev
                JOIN episode e ON e.id = ev.id_episode
                JOIN serie   s ON s.id = e.id_serie
                WHERE ev.id_user = :u
                ORDER BY ev.updated_at DESC";
        $st = $pdo->prepare($sql);
        $st->execute([':u'=>$idUser]);
        $rows = $st->fetchAll(PDO::FETCH_ASSOC);

        $seen = []; $out = [];
        foreach ($rows as $r) {
            $sid = (int)$r['id_serie'];
            if (!isset($seen[$sid])) { $seen[$sid]=1; $out[]=$r; }
        }
        return $out;
    }
}
