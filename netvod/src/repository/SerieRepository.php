<?php
namespace netvod\repository;

use netvod\model\Serie;
use PDO;

class SerieRepository
{
    public function findAll(): array
    {
        $pdo = ConnectionFactory::getConnection();
        $query = "SELECT * FROM serie";
        $stmt = $pdo->query($query);
        $stmt->setFetchMode(PDO::FETCH_CLASS, Serie::class);
        return $stmt->fetchAll();
    }

    public function findById(int $id): ?Serie
    {
        $pdo = ConnectionFactory::getConnection();
        $stmt = $pdo->prepare("SELECT * FROM serie WHERE id = ?");
        $stmt->execute([$id]);
        $stmt->setFetchMode(PDO::FETCH_CLASS, \netvod\model\Serie::class);
        return $stmt->fetch() ?: null;
    }

}
