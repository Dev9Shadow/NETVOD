<?php
namespace netvod\repository;

use PDO;

class FavoriRepository
{
    /**
     * Ajouter une série aux favoris
     */
    public function add(int $userId, int $serieId): bool
    {
        try {
            $pdo = ConnectionFactory::getConnection();
            $stmt = $pdo->prepare("INSERT INTO favorite (id_user, id_serie) VALUES (?, ?)");
            return $stmt->execute([$userId, $serieId]);
        } catch (\PDOException $e) {
            // Si déjà en favori, retourne false
            return false;
        }
    }

    /**
     * Retirer une série des favoris
     */
    public function remove(int $userId, int $serieId): bool
    {
        $pdo = ConnectionFactory::getConnection();
        $stmt = $pdo->prepare("DELETE FROM favorite WHERE id_user = ? AND id_serie = ?");
        return $stmt->execute([$userId, $serieId]);
    }

    /**
     * Vérifier si une série est en favori
     */
    public function isFavorite(int $userId, int $serieId): bool
    {
        $pdo = ConnectionFactory::getConnection();
        $stmt = $pdo->prepare("SELECT COUNT(*) FROM favorite WHERE id_user = ? AND id_serie = ?");
        $stmt->execute([$userId, $serieId]);
        return $stmt->fetchColumn() > 0;
    }

    /**
     * Récupérer toutes les séries favorites d'un utilisateur
     */
    public function getUserFavorites(int $userId): array
    {
        $pdo = ConnectionFactory::getConnection();
        $query = "SELECT s.* FROM serie s 
                  INNER JOIN favorite f ON s.id = f.id_serie 
                  WHERE f.id_user = ? 
                  ORDER BY s.titre";
        $stmt = $pdo->prepare($query);
        $stmt->execute([$userId]);
        $stmt->setFetchMode(PDO::FETCH_CLASS, 'netvod\entity\Serie');
        return $stmt->fetchAll();
    }

    /**
     * Compter le nombre de favoris d'un utilisateur
     */
    public function countUserFavorites(int $userId): int
    {
        $pdo = ConnectionFactory::getConnection();
        $stmt = $pdo->prepare("SELECT COUNT(*) FROM favorite WHERE id_user = ?");
        $stmt->execute([$userId]);
        return (int)$stmt->fetchColumn();
    }

    public function getUserFavoriteIds(int $userId): array
    {
        $pdo = ConnectionFactory::getConnection();
        $stmt = $pdo->prepare("SELECT id_serie FROM favorite WHERE id_user = ?");
        $stmt->execute([$userId]);
        return $stmt->fetchAll(PDO::FETCH_COLUMN);
    }
}