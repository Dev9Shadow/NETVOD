<?php
namespace netvod\repository;

use netvod\entity\Serie;
use PDO;

class SerieRepository
{
    public function findAll(): array
    {
        $pdo = ConnectionFactory::getConnection();
        $query = "SELECT * FROM serie";
        $stmt = $pdo->query($query);
        
        $series = [];
        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $serie = new Serie();
            $serie->id = (int)$row['id'];
            $serie->titre = $row['titre'];
            $serie->descriptif = $row['descriptif'] ?? '';
            $serie->img = $row['img'] ?? '';
            $serie->annee = (int)($row['annee'] ?? 0);
            $serie->date_ajout = $row['date_ajout'] ?? '';
            $serie->descriptif = $row['descriptif'] ?? null;
            $serie->genre = $row['genre'] ?? null;
            $serie->img = $row['img'] ?? null;
            
            $series[] = $serie;
        }
        
        return $series;
    }

    public function findById(int $id): ?Serie
    {
        $pdo = ConnectionFactory::getConnection();
        $stmt = $pdo->prepare("SELECT * FROM serie WHERE id = ?");
        $stmt->execute([$id]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if (!$row) {
            return null;
        }
        
        $serie = new Serie();
        $serie->id = (int)$row['id'];
        $serie->titre = $row['titre'];
        $serie->descriptif = $row['descriptif'] ?? '';
        $serie->img = $row['img'] ?? '';
        $serie->annee = (int)($row['annee'] ?? 0);
        $serie->date_ajout = $row['date_ajout'] ?? '';
        $serie->descriptif = $row['descriptif'] ?? null;
        $serie->genre = $row['genre'] ?? null;
        $serie->img = $row['img'] ?? null;
        
        return $serie;
    }
}