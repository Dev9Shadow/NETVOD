<?php
namespace netvod\repository;

use netvod\entity\PublicCible;
use PDO;

class PublicCibleRepository
{
    public function findAll(): array
    {
        $pdo = ConnectionFactory::getConnection();
        $query = "SELECT * FROM public_cible ORDER BY nom";
        $stmt = $pdo->query($query);
        
        $publics = [];
        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $public = new PublicCible();
            $public->id = (int)$row['id'];
            $public->nom = $row['nom'];
            
            $publics[] = $public;
        }
        
        return $publics;
    }

    public function findById(int $id): ?PublicCible
    {
        $pdo = ConnectionFactory::getConnection();
        $stmt = $pdo->prepare("SELECT * FROM public_cible WHERE id = ?");
        $stmt->execute([$id]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if (!$row) {
            return null;
        }
        
        $public = new PublicCible();
        $public->id = (int)$row['id'];
        $public->nom = $row['nom'];
        
        return $public;
    }
}