<?php
namespace netvod\repository;

use netvod\entity\User;
use PDO;

class UserRepository
{
    public function findByEmail(string $email): ?User
    {
        $pdo = ConnectionFactory::getConnection();
        $stmt = $pdo->prepare("SELECT * FROM user WHERE email = ?");
        $stmt->execute([$email]);
        
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        if (!$row) {
            return null;
        }
        
        $user = new User();
        $user->id = (int)$row['id'];
        $user->email = $row['email'];
        $user->password_hash = $row['password_hash'];
        $user->nom = $row['nom'] ?? '';
        $user->prenom = $row['prenom'] ?? '';
        $user->created_at = $row['created_at'] ?? null;
        $user->is_active = (bool)($row['is_active'] ?? 1);
        
        return $user;
    }

    public function save(User $user): bool
    {
        $pdo = ConnectionFactory::getConnection();
        $stmt = $pdo->prepare(
            "INSERT INTO user (email, password_hash, nom, prenom, created_at, is_active) 
             VALUES (?, ?, ?, ?, NOW(), 1)"
        );
        
        return $stmt->execute([
            $user->email,
            $user->password_hash,
            $user->nom,
            $user->prenom
        ]);
    }

    public function findById(int $id): ?User
    {
        $pdo = ConnectionFactory::getConnection();
        $stmt = $pdo->prepare("SELECT * FROM user WHERE id = ?");
        $stmt->execute([$id]);
        
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        if (!$row) {
            return null;
        }
        
        $user = new User();
        $user->id = (int)$row['id'];
        $user->email = $row['email'];
        $user->password_hash = $row['password_hash'];
        $user->nom = $row['nom'] ?? '';
        $user->prenom = $row['prenom'] ?? '';
        $user->created_at = $row['created_at'] ?? null;
        $user->is_active = (bool)($row['is_active'] ?? 1);
        
        return $user;
    }
}