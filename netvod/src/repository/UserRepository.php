<?php
namespace netvod\repository;

use netvod\model\User;
use PDO;

class UserRepository
{
    public function create(string $email, string $password, ?string $nom, ?string $prenom): bool
    {
        $pdo = ConnectionFactory::getConnection();
        $hash = password_hash($password, PASSWORD_DEFAULT);
        $stmt = $pdo->prepare("INSERT INTO utilisateur (email, password, nom, prenom) VALUES (?, ?, ?, ?)");
        return $stmt->execute([$email, $hash, $nom, $prenom]);
    }

    public function findByEmail(string $email): ?User
    {
        $pdo = ConnectionFactory::getConnection();
        $stmt = $pdo->prepare("SELECT * FROM utilisateur WHERE email = ?");
        $stmt->execute([$email]);
        $stmt->setFetchMode(PDO::FETCH_CLASS, User::class);
        return $stmt->fetch() ?: null;
    }
}
