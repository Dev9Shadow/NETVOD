<?php
namespace netvod\action;

use netvod\renderer\Layout;
use netvod\repository\ConnectionFactory;
use netvod\repository\UserRepository;
use netvod\util\PasswordValidator;

class ProfileAction
{
    public function execute(): string
    {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }

        if (!isset($_SESSION['user_id'])) {
            header('Location: index.php?action=login');
            exit;
        }

        ConnectionFactory::setConfig(__DIR__ . '/../../config/db.config.ini');

        $repo = new UserRepository();
        $user = $repo->findById((int)$_SESSION['user_id']);
        if (!$user) {
            header('Location: index.php?action=login');
            exit;
        }

        $infoSuccess = $infoError = $pwdSuccess = $pwdError = null;

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $formType = $_POST['form_type'] ?? '';

            if ($formType === 'info') {
                $prenom = trim($_POST['prenom'] ?? '');
                $nom = trim($_POST['nom'] ?? '');
                $email = trim($_POST['email'] ?? '');

                if ($prenom === '' || $nom === '' || $email === '') {
                    $infoError = "Veuillez remplir tous les champs.";
                } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
                    $infoError = "Adresse email invalide.";
                } else {
                    $other = $repo->findByEmail($email);
                    if ($other && $other->id !== $user->id) {
                        $infoError = "Cet email est déjà utilisé.";
                    } else {
                        if ($repo->updateInfo($user->id, $email, $nom, $prenom)) {
                            $user = $repo->findById($user->id) ?? $user;
                            $_SESSION['user_email'] = $user->email;
                            $_SESSION['user_nom'] = $user->nom;
                            $_SESSION['user_prenom'] = $user->prenom;
                            $infoSuccess = "Informations mises à jour.";
                        } else {
                            $infoError = "Impossible de mettre à jour les informations.";
                        }
                    }
                }
            } elseif ($formType === 'password') {
                $current = $_POST['current_password'] ?? '';
                $new = $_POST['new_password'] ?? '';
                $confirm = $_POST['confirm_password'] ?? '';

                if ($new === '' || $confirm === '' || $current === '') {
                    $pwdError = "Veuillez remplir tous les champs.";
                } elseif ($new !== $confirm) {
                    $pwdError = "Les mots de passe ne correspondent pas.";
                } else {
                    $policyErrors = PasswordValidator::validate($new);
                    if (!empty($policyErrors)) {
                        $pwdError = implode(' ', $policyErrors);
                    } else {
                        $fresh = $repo->findById($user->id);
                        if (!$fresh || !password_verify($current, (string)$fresh->password_hash)) {
                            $pwdError = "Mot de passe actuel incorrect.";
                        } else {
                            $hash = password_hash($new, PASSWORD_DEFAULT);
                            if ($repo->updatePassword($user->id, $hash)) {
                                $pwdSuccess = "Mot de passe mis à jour.";
                            } else {
                                $pwdError = "Échec de la mise à jour du mot de passe.";
                            }
                        }
                    }
                }
            }
        }

        $html = "<h1>Mon profil</h1>";

        if ($infoError) {
            $html .= "<p class='error'>" . htmlspecialchars($infoError) . "</p>";
        }
        if ($infoSuccess) {
            $html .= "<p class='success'>" . htmlspecialchars($infoSuccess) . "</p>";
        }

        $html .= "
        <section class='card'>
            <h2>Informations personnelles</h2>
            <form method='POST'>
                <input type='hidden' name='form_type' value='info'>
                <div>
                    <label>Prénom</label>
                    <input type='text' name='prenom' value='" . htmlspecialchars((string)$user->prenom) . "' required>
                </div>
                <div>
                    <label>Nom</label>
                    <input type='text' name='nom' value='" . htmlspecialchars((string)$user->nom) . "' required>
                </div>
                <div>
                    <label>Email</label>
                    <input type='email' name='email' value='" . htmlspecialchars((string)$user->email) . "' required>
                </div>
                <button type='submit'>Enregistrer</button>
            </form>
        </section>
        ";

        if ($pwdError) {
            $html .= "<p class='error'>" . htmlspecialchars($pwdError) . "</p>";
        }
        if ($pwdSuccess) {
            $html .= "<p class='success'>" . htmlspecialchars($pwdSuccess) . "</p>";
        }

        $html .= "
        <section class='card'>
            <h2>Changer le mot de passe</h2>
            <form method='POST'>
                <input type='hidden' name='form_type' value='password'>
                <div>
                    <label>Mot de passe actuel</label>
                    <input type='password' name='current_password' required>
                </div>
                <div>
                    <label>Nouveau mot de passe</label>
                    <input type='password' name='new_password' minlength='8' required>
                </div>
                <div>
                    <label>Confirmer le nouveau mot de passe</label>
                    <input type='password' name='confirm_password' minlength='8' required>
                </div>
                <button type='submit'>Mettre à jour</button>
            </form>
        </section>
        ";

        $html .= "
        <section class='card' style='border-color:#c0392b;'>
            <h2>Session</h2>
            <p><a class='btn' href='index.php?action=logout'>Se déconnecter</a></p>
        </section>
        ";

        return Layout::render($html, 'Mon profil - NETVOD');
    }
}

