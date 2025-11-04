<?php
namespace netvod\action;

use netvod\repository\ConnectionFactory;
use netvod\repository\UserRepository;

class RegisterAction
{
    public function execute(): string
    {
        ConnectionFactory::setConfig(__DIR__ . '/../../config/db.config.ini');
        $html = "<h1>Inscription</h1>";

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $email = trim($_POST['email']);
            $password = trim($_POST['password']);
            $nom = trim($_POST['nom']);
            $prenom = trim($_POST['prenom']);

            $repo = new UserRepository();
            $existing = $repo->findByEmail($email);

            if ($existing) {
                $html .= "<p style='color:red'>Un compte existe déjà avec cet email.</p>";
            } else {
                $repo->create($email, $password, $nom, $prenom);
                $html .= "<p style='color:green'>Compte créé avec succès ! Vous pouvez maintenant vous connecter.</p>";
            }
        }

        $html .= <<<HTML
        <form method="POST">
            <label>Email :</label><br>
            <input type="email" name="email" required><br><br>

            <label>Mot de passe :</label><br>
            <input type="password" name="password" required><br><br>

            <label>Nom :</label><br>
            <input type="text" name="nom"><br><br>

            <label>Prénom :</label><br>
            <input type="text" name="prenom"><br><br>

            <button type="submit">Créer le compte</button>
        </form>
        <p><a href='index.php'>Retour à l'accueil</a></p>
        HTML;

        return $html;
    }
}
