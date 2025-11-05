<?php
namespace netvod\action;

use netvod\repository\ConnectionFactory;
use netvod\repository\UserRepository;
use netvod\entity\User;
use netvod\renderer\Layout;

class RegisterAction
{
    public function execute(): string
    {
        ConnectionFactory::setConfig(__DIR__ . '/../../config/db.config.ini');
        
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $email = $_POST['email'] ?? '';
            $password = $_POST['password'] ?? '';
            $nom = $_POST['nom'] ?? '';
            $prenom = $_POST['prenom'] ?? '';
            
            $repo = new UserRepository();
            
            // Vérifier si l'email existe déjà
            if ($repo->findByEmail($email)) {
                $error = "Cet email est déjà utilisé.";
            } else {
                // Créer le nouvel utilisateur
                $user = new User();
                $user->email = $email;
                $user->password_hash = password_hash($password, PASSWORD_DEFAULT);
                $user->nom = $nom;
                $user->prenom = $prenom;
                
                if ($repo->save($user)) {
                    $success = "Inscription réussie ! Vous pouvez maintenant vous connecter.";
                } else {
                    $error = "Une erreur est survenue lors de l'inscription.";
                }
            }
        }
        
        // Formulaire d'inscription
        $html = "<h1>Inscription</h1>";
        
        if (isset($error)) {
            $html .= "<p class='error'>{$error}</p>";
        }
        
        if (isset($success)) {
            $html .= "<p class='success'>{$success}</p>";
            $html .= "<p style='text-align: center;'><a href='index.php?action=login' class='btn'>Se connecter</a></p>";
        } else {
            $html .= "
                <form method='POST'>
                    <div>
                        <label>Prénom :</label>
                        <input type='text' name='prenom' required>
                    </div>
                    <div>
                        <label>Nom :</label>
                        <input type='text' name='nom' required>
                    </div>
                    <div>
                        <label>Email :</label>
                        <input type='email' name='email' required>
                    </div>
                    <div>
                        <label>Mot de passe :</label>
                        <input type='password' name='password' required minlength='6'>
                    </div>
                    <button type='submit'>S'inscrire</button>
                </form>
                <p style='text-align: center; margin-top: 20px;'>
                    <a href='index.php?action=login'>Déjà un compte ? Se connecter</a>
                </p>
            ";
        }
        
        return Layout::render($html, "Inscription - NETVOD");
    }
}