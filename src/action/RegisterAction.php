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
            $email = trim($_POST['email'] ?? '');
            $password = $_POST['password'] ?? '';
            $password_confirm = $_POST['password_confirm'] ?? '';
            $nom = trim($_POST['nom'] ?? '');
            $prenom = trim($_POST['prenom'] ?? '');
            
            // Validation
            $errors = [];
            
            if (empty($email) || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
                $errors[] = "Email invalide.";
            }
            
            // Validation du mot de passe sécurisé
            if (empty($password)) {
                $errors[] = "Le mot de passe est obligatoire.";
            } else {
                if (strlen($password) < 8) {
                    $errors[] = "Le mot de passe doit contenir au moins 8 caractères.";
                }
                if (!preg_match('/[a-z]/', $password)) {
                    $errors[] = "Le mot de passe doit contenir au moins une minuscule.";
                }
                if (!preg_match('/[A-Z]/', $password)) {
                    $errors[] = "Le mot de passe doit contenir au moins une majuscule.";
                }
                if (!preg_match('/[0-9]/', $password)) {
                    $errors[] = "Le mot de passe doit contenir au moins un chiffre.";
                }
                if (!preg_match('/[@#$%^&*()!,.?]/', $password)) {
                    $errors[] = "Le mot de passe doit contenir au moins un caractère spécial (!@#$%^&*...)";
                }
            }
            
            if ($password !== $password_confirm) {
                $errors[] = "Les mots de passe ne correspondent pas.";
            }
            
            if (empty($nom) || empty($prenom)) {
                $errors[] = "Le nom et le prénom sont obligatoires.";
            }
            
            if (empty($errors)) {
                $repo = new UserRepository();
                
                // Vérifier si l'email existe déjà
                if ($repo->findByEmail($email)) {
                    $errors[] = "Cet email est déjà utilisé.";
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
                        $errors[] = "Une erreur est survenue lors de l'inscription.";
                    }
                }
            }
        }
        
        // Formulaire d'inscription
        $html = "<h1>Inscription</h1>";
        
        if (!empty($errors)) {
            $html .= "<div class='error' style='background: #f8d7da; color: #721c24; padding: 15px; border-radius: 5px; margin-bottom: 20px;'>";
            foreach ($errors as $error) {
                $html .= "<p style='margin: 5px 0;'>{$error}</p>";
            }
            $html .= "</div>";
        }
        
        if (isset($success)) {
            $html .= "<div class='success' style='background: #d4edda; color: #155724; padding: 15px; border-radius: 5px; margin-bottom: 20px;'>
                        <p style='margin: 0;'>{$success}</p>
                      </div>";
            $html .= "<p style='text-align: center; margin-top: 20px;'>
                        <a href='index.php?action=login' class='btn' style='display: inline-block; padding: 10px 20px; background: #28a745; color: white; text-decoration: none; border-radius: 5px;'>
                            Se connecter
                        </a>
                      </p>";
        } else {
            $email_value = htmlspecialchars($_POST['email'] ?? '');
            $nom_value = htmlspecialchars($_POST['nom'] ?? '');
            $prenom_value = htmlspecialchars($_POST['prenom'] ?? '');
            
            $html .= "
                <form method='POST'>
                    <div>
                        <label>Prénom :</label>
                        <input type='text' name='prenom' value='{$prenom_value}' required>
                    </div>
                    <div>
                        <label>Nom :</label>
                        <input type='text' name='nom' value='{$nom_value}' required>
                    </div>
                    <div>
                        <label>Email :</label>
                        <input type='email' name='email' value='{$email_value}' required>
                    </div>
                    <div>
                        <label>Mot de passe :</label>
                        <input type='password' name='password' required minlength='8'>
                    </div>
                    <div>
                        <label>Confirmer le mot de passe :</label>
                        <input type='password' name='password_confirm' required minlength='8'>
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