<?php
namespace netvod\repository;

use PDO;

class PasswordResetRepository
{
    
    public function createToken(int $userId): string
    {
        $pdo = ConnectionFactory::getConnection();

        // Générer un token aléatoire unique
        $token = bin2hex(random_bytes(32));

        $stmt = $pdo->prepare(
            "INSERT INTO password_reset (user_id, token, created_at, expires_at, used) 
            VALUES (?, ?, NOW(), DATE_ADD(NOW(), INTERVAL 1 HOUR), 0)"
        );

        if (!$stmt->execute([$userId, $token])) {
            throw new \Exception('Impossible de créer le token: ' . implode(' | ', $stmt->errorInfo()));
        }

        return $token;
    }

    
    public function validateToken(string $token): ?int
    {
        $pdo = ConnectionFactory::getConnection();
        
        $stmt = $pdo->prepare(
            "SELECT user_id FROM password_reset 
             WHERE token = ? 
             AND used = FALSE 
             AND expires_at > NOW()"
        );
        $stmt->execute([$token]);
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        
        return $result ? (int)$result['user_id'] : null;
    }
    
   
    public function markTokenAsUsed(string $token): bool
    {
        $pdo = ConnectionFactory::getConnection();
        
        $stmt = $pdo->prepare("UPDATE password_reset SET used = TRUE WHERE token = ?");
        return $stmt->execute([$token]);
    }
    
    
    public function cleanExpiredTokens(): int
    {
        $pdo = ConnectionFactory::getConnection();
        
        $stmt = $pdo->prepare("DELETE FROM password_reset WHERE expires_at < NOW()");
        $stmt->execute();
        
        return $stmt->rowCount();
    }
}