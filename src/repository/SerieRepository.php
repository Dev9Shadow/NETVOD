<?php
namespace netvod\repository;

use netvod\entity\Serie;
use netvod\entity\PublicCible;
use PDO;

class SerieRepository
{
    public function findAll(): array
    {
        $pdo = ConnectionFactory::getConnection();
        $query = "SELECT s.*, pc.nom as public_nom 
                  FROM serie s 
                  LEFT JOIN public_cible pc ON s.id_public_cible = pc.id";
        $stmt = $pdo->query($query);
        
        $series = [];
        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $serie = new Serie();
            $serie->id = (int)$row['id'];
            $serie->titre = $row['titre'];
            $serie->descriptif = $row['descriptif'] ?? null;
            $serie->img = $row['img'] ?? null;
            $serie->annee = isset($row['annee']) ? (int)$row['annee'] : null;
            $serie->date_ajout = $row['date_ajout'] ?? null;
            $serie->genre = $row['genre'] ?? null;
            $serie->id_public_cible = isset($row['id_public_cible']) ? (int)$row['id_public_cible'] : null;
            
            // Charger le public cible si existe
            if ($row['public_nom']) {
                $public = new PublicCible();
                $public->id = $serie->id_public_cible;
                $public->nom = $row['public_nom'];
                $serie->publicCible = $public;
            }
            
            $series[] = $serie;
        }
        
        return $series;
    }

    public function findById(int $id): ?Serie
    {
        $pdo = ConnectionFactory::getConnection();
        $stmt = $pdo->prepare("SELECT s.*, pc.nom as public_nom 
                               FROM serie s 
                               LEFT JOIN public_cible pc ON s.id_public_cible = pc.id 
                               WHERE s.id = ?");
        $stmt->execute([$id]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if (!$row) {
            return null;
        }
        
        $serie = new Serie();
        $serie->id = (int)$row['id'];
        $serie->titre = $row['titre'];
        $serie->descriptif = $row['descriptif'] ?? null;
        $serie->img = $row['img'] ?? null;
        $serie->annee = isset($row['annee']) ? (int)$row['annee'] : null;
        $serie->date_ajout = $row['date_ajout'] ?? null;
        $serie->genre = $row['genre'] ?? null;
        $serie->id_public_cible = isset($row['id_public_cible']) ? (int)$row['id_public_cible'] : null;
        
        // Charger le public cible si existe
        if ($row['public_nom']) {
            $public = new PublicCible();
            $public->id = $serie->id_public_cible;
            $public->nom = $row['public_nom'];
            $serie->publicCible = $public;
        }
        
        return $serie;
    }

    public function countEpisodes(int $serieId): int
    {
        $pdo = ConnectionFactory::getConnection();
        $stmt = $pdo->prepare("SELECT COUNT(*) as total FROM episode WHERE id_serie = ?");
        $stmt->execute([$serieId]);
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        return (int)($result['total'] ?? 0);
    }

    /**
     * Rechercher des séries par mots-clés dans le titre ou le descriptif
     */
    public function search(string $keywords): array
    {
        $pdo = ConnectionFactory::getConnection();
        
        // Préparer la recherche (insensible à la casse)
        $searchTerm = '%' . $keywords . '%';
        
        $query = "SELECT s.*, pc.nom as public_nom 
                  FROM serie s 
                  LEFT JOIN public_cible pc ON s.id_public_cible = pc.id
                  WHERE s.titre LIKE ? OR s.descriptif LIKE ?
                  ORDER BY s.titre";
        
        $stmt = $pdo->prepare($query);
        $stmt->execute([$searchTerm, $searchTerm]);
        
        $series = [];
        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $serie = new Serie();
            $serie->id = (int)$row['id'];
            $serie->titre = $row['titre'];
            $serie->descriptif = $row['descriptif'] ?? null;
            $serie->img = $row['img'] ?? null;
            $serie->annee = isset($row['annee']) ? (int)$row['annee'] : null;
            $serie->date_ajout = $row['date_ajout'] ?? null;
            $serie->genre = $row['genre'] ?? null;
            $serie->id_public_cible = isset($row['id_public_cible']) ? (int)$row['id_public_cible'] : null;
            
            // Charger le public cible si existe
            if ($row['public_nom']) {
                $public = new PublicCible();
                $public->id = $serie->id_public_cible;
                $public->nom = $row['public_nom'];
                $serie->publicCible = $public;
            }
            
            $series[] = $serie;
        }
        
        return $series;
    }
}