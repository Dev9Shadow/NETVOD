<?php
namespace netvod\action;

use netvod\repository\ConnectionFactory;
use netvod\repository\UserRepository;
 

class LoginAction
{
    public string $title = '';
    public function execute(): string
    {
        ConnectionFactory::setConfig(__DIR__ . '/../../config/db.config.ini');
        
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $email = $_POST['email'] ?? '';
            $password = $_POST['password'] ?? '';
            
            $repo = new UserRepository();
            $user = $repo->findByEmail($email);
            
            if ($user && password_verify($password, $user->password_hash)) {
                // Connexion réussie
                if (session_status() === PHP_SESSION_NONE) {
                    session_start();
                }
                $_SESSION['user_id'] = $user->id;
                $_SESSION['user_email'] = $user->email;
                $_SESSION['user_nom'] = $user->nom;
                $_SESSION['user_prenom'] = $user->prenom;
                
                header('Location: index.php');
                exit;
            } else {
                $error = "Email ou mot de passe incorrect.";
            }
        }
        
        $html = "<h1>Connexion</h1>";
        
        if (isset($error)) {
            $html .= "<p class='error'>{$error}</p>";
        }
        
        $html .= "
            <form method='POST'>
                <div>
                    <label>Email :</label>
                    <input type='email' name='email' required>
                </div>
                <div>
                    <label>Mot de passe :</label>
                    <input type='password' name='password' required>
                </div>
                <button type='submit'>Se connecter</button>
            </form>
            <p style='text-align: center; margin-top: 20px;'>
                <a href='index.php?action=register'>Pas encore de compte ? S'inscrire</a>
            </p>
            <p style='text-align: center; margin-top: 15px;'>
                <a href='index.php?action=forgotpassword'>Mot de passe oublié ?</a>
            </p>
        ";
        
        $this->title = "Connexion - NETVOD";
        return $html;
    }
}
