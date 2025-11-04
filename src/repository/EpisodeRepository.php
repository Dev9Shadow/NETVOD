<?php
namespace netvod\repository;

use netvod\model\Episode;
use PDO;

class EpisodeRepository
{
    public function findBySerie(int $idSerie): array
    {
        $pdo = ConnectionFactory::getConnection();
        $query = "SELECT * FROM episode WHERE serie_id = ?";
        $stmt = $pdo->prepare($query);
        $stmt->execute([$idSerie]);
        $stmt->setFetchMode(PDO::FETCH_CLASS, Episode::class);
        return $stmt->fetchAll();
    }

    public function findById(int $id): ?Episode
    {
        $pdo = ConnectionFactory::getConnection();
        $stmt = $pdo->prepare("SELECT * FROM episode WHERE id = ?");
        $stmt->execute([$id]);
        $stmt->setFetchMode(PDO::FETCH_CLASS, Episode::class);
        return $stmt->fetch() ?: null;
    }
}
