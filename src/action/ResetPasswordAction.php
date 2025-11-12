<?php
namespace netvod\action;
use netvod\repository\ConnectionFactory;
use netvod\repository\UserRepository;
use netvod\repository\PasswordResetRepository;
use netvod\util\PasswordValidator;

class ResetPasswordAction
{
    public string $title = '';
    public function execute(): string
    {
        ConnectionFactory::setConfig(__DIR__ . '/../../config/db.config.ini');
        
        $token = $_GET['token'] ?? '';
        $error = $success = null;
        
        if (empty($token)) {
            $this->title = "Erreur - NETVOD";
            return "<h1>Lien invalide</h1>
                <p>Le lien de réinitialisation est invalide ou a expiré.</p>
                <p><a href='index.php?action=forgot-password' class='btn'>Demander un nouveau lien</a></p>";
        }
        
        // Vérifier que le token est valide
        $resetRepo = new PasswordResetRepository();
        $userId = $resetRepo->validateToken($token);
        
        if (!$userId) {
            $this->title = "Erreur - NETVOD";
            return "<h1>Lien expiré</h1>
                <p>Ce lien de réinitialisation a expiré ou a déjà été utilisé.</p>
                <p><a href='index.php?action=forgot-password' class='btn'>Demander un nouveau lien</a></p>";
        }
        
        // Traiter le formulaire
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $newPassword = $_POST['new_password'] ?? '';
            $confirmPassword = $_POST['confirm_password'] ?? '';
            
            if (empty($newPassword) || empty($confirmPassword)) {
                $error = "Veuillez remplir tous les champs.";
            } elseif ($newPassword !== $confirmPassword) {
                $error = "Les mots de passe ne correspondent pas.";
            } else {
                $policyErrors = PasswordValidator::validate($newPassword);
                if (!empty($policyErrors)) {
                    $error = implode(' ', $policyErrors);
                } else {
                    // Mettre à jour le mot de passe
                    $userRepo = new UserRepository();
                    $hash = password_hash($newPassword, PASSWORD_DEFAULT);
                    
                    if ($userRepo->updatePassword($userId, $hash)) {
                        // Marquer le token comme utilisé
                        $resetRepo->markTokenAsUsed($token);
                        
                        $this->title = "Succès - NETVOD";
                        return "<h1>Mot de passe réinitialisé</h1>
                            <p class='success'>Votre mot de passe a été réinitialisé avec succès !</p>
                            <p><a href='index.php?action=login' class='btn'>Se connecter</a></p>";
                    } else {
                        $error = "Erreur lors de la réinitialisation du mot de passe.";
                    }
                }
            }
        }
        
        $html = "<h1>Nouveau mot de passe</h1>";
        
        if ($error) {
            $html .= "<p class='error'>" . htmlspecialchars($error) . "</p>";
        }
        
        $html .= "
        <form method='POST'>
            <p style='color: #b3b3b3; margin-bottom: 20px;'>
                Saisissez votre nouveau mot de passe.
            </p>
            
            <label>Nouveau mot de passe</label>
            <input type='password' name='new_password' minlength='8' required>
            
            <label>Confirmer le mot de passe</label>
            <input type='password' name='confirm_password' minlength='8' required>
            
            <button type='submit'>Réinitialiser</button>
        </form>
        ";
        
        $this->title = "Nouveau mot de passe - NETVOD";
        return $html;
    }
}
