<?php
namespace netvod\action;

use netvod\renderer\Layout;
use netvod\repository\ConnectionFactory;
use netvod\repository\UserRepository;
use netvod\repository\PasswordResetRepository;

class ForgotPasswordAction
{
    public function execute(): string
    {
        ConnectionFactory::setConfig(__DIR__ . '/../../config/db.config.ini');
        
        $error = $success = null;
        
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $email = trim($_POST['email'] ?? '');
            
            if (empty($email)) {
                $error = "Veuillez saisir votre adresse email.";
            } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
                $error = "Adresse email invalide.";
            } else {
                $userRepo = new UserRepository();
                $user = $userRepo->findByEmail($email);
                
                if ($user) {
                    $resetRepo = new PasswordResetRepository();
                    $token = $resetRepo->createToken($user->id);
                    
                    // Générer l'URL de réinitialisation
                    $resetUrl = "http://" . $_SERVER['HTTP_HOST'] . dirname($_SERVER['PHP_SELF']) . "/index.php?action=resetpassword&token=" . $token;
                    
                    // TODO: Envoyer l'email (pour l'instant, on affiche juste l'URL)
                    $success = "Un lien de réinitialisation a été généré. <br><br>
                                <strong>URL de réinitialisation :</strong><br>
                                <a href='{$resetUrl}' style='color: #e50914; word-break: break-all;'>{$resetUrl}</a><br><br>
                                <small style='color: #999;'>Ce lien expire dans 1 heure.</small>";
                } else {
                    // Par sécurité, on ne dit pas si l'email existe ou non
                    $success = "Si cette adresse email existe, un lien de réinitialisation a été envoyé.";
                }
            }
        }
        
        $html = "<h1>Mot de passe oublié</h1>";
        
        if ($error) {
            $html .= "<p class='error'>" . htmlspecialchars($error) . "</p>";
        }
        
        if ($success) {
            $html .= "<p class='success'>{$success}</p>";
        }
        
        $html .= "
        <form method='POST'>
            <p style='color: #b3b3b3; margin-bottom: 20px;'>
                Saisissez votre adresse email pour recevoir un lien de réinitialisation de mot de passe.
            </p>
            
            <label>Adresse email</label>
            <input type='email' name='email' placeholder='votre@email.com' required>
            
            <button type='submit'>Envoyer le lien</button>
            
            <p style='text-align: center; margin-top: 20px;'>
                <a href='index.php?action=login'>Retour à la connexion</a>
            </p>
        </form>
        ";
        
        return Layout::render($html, "Mot de passe oublié - NETVOD");
    }
}