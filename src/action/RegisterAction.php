<?php
namespace netvod\action;

use netvod\repository\ConnectionFactory;
use netvod\repository\UserRepository;
use netvod\entity\User;
use netvod\renderer\Layout;
use netvod\util\PasswordValidator;

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

            $errors = [];

            if ($email === '' || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
                $errors[] = 'Email invalide.';
            }

            $errors = array_merge($errors, PasswordValidator::validate($password));

            if ($password !== $password_confirm) {
                $errors[] = 'Les mots de passe ne correspondent pas.';
            }

            if ($nom === '' || $prenom === '') {
                $errors[] = 'Le nom et le prénom sont obligatoires.';
            }

            if (empty($errors)) {
                $repo = new UserRepository();
                if ($repo->findByEmail($email)) {
                    $errors[] = 'Cet email est déjà utilisé.';
                } else {
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

        $html = '<h1>Inscription</h1>';

        if (!empty($errors)) {
            $html .= "<div class='error'>";
            foreach ($errors as $error) {
                $html .= '<p style="margin:5px 0;">' . htmlspecialchars($error) . '</p>';
            }
            $html .= '</div>';
        }

        if (isset($success)) {
            $html .= "<div class='success'><p style='margin:0;'>{$success}</p></div>";
            $html .= "<p style='text-align:center; margin-top:20px;'>
                <a href='index.php?action=login' class='btn'>Se connecter</a>
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

        return Layout::render($html, 'Inscription - NETVOD');
    }
}

