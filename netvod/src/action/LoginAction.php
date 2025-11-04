<?php
namespace netvod\action;

use netvod\repository\ConnectionFactory;
use netvod\repository\UserRepository;

class LoginAction
{
    public function execute(): string
    {
        session_start();
        ConnectionFactory::setConfig(__DIR__ . '/../../config/db.config.ini');
        $html = "<h1>Connexion</h1>";

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $email = trim($_POST['email']);
            $password = trim($_POST['password']);

            $repo = new UserRepository();
            $user = $repo->findByEmail($email);

            if ($user && password_verify($password, $user->password)) {
                $_SESSION['user'] = [
                    'id' => $user->id,
                    'email' => $user->email,
                    'nom' => $user->nom,
                    'prenom' => $user->prenom
                ];
                $html .= "<p style='color:green'>Connexion réussie</p>
                          <p><a href='index.php'>Retour à l'accueil</a></p>";
                return $html;
            } else {
                $html .= "<p style='color:red'>Email ou mot de passe incorrect.</p>";
            }
        }

        $html .= <<<HTML
        <form method="POST">
            <label>Email :</label><br>
            <input type="email" name="email" required><br><br>

            <label>Mot de passe :</label><br>
            <input type="password" name="password" required><br><br>

            <button type="submit">Se connecter</button>
        </form>
        <p><a href='index.php?action=register'>Créer un compte</a></p>
        HTML;

        return $html;
    }
}
