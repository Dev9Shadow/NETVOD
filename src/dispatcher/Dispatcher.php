<?php
namespace netvod\dispatcher;

use netvod\action\DefaultAction;
use netvod\auth\AuthnProvided;
use netvod\auth\Authz;

ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

class Dispatcher
{
    public function run(): void
    {
        $actionName = $_GET['action'] ?? 'default';
        $actionClass = 'netvod\\action\\' . ucfirst($actionName) . 'Action';

        // Vérifier si l'utilisateur est authentifié
        if (!AuthnProvided::isAuthenticated()) {
            // Si l'action n'est pas publique, rediriger vers login
            if (!Authz::isAllowed($actionName)) {
                if (session_status() === PHP_SESSION_NONE) {
                    session_start();
                }
                $_SESSION['flash_error'] = 'Veuillez vous connecter pour continuer';
                header('Location: index.php?action=login');
                exit;
            }
        }

        // Si la classe n'existe pas, utiliser l'action par défaut
        if (!class_exists($actionClass)) {
            $actionClass = DefaultAction::class;
        }

        // Instancier et exécuter l'action
        $action = new $actionClass();
        echo $action->execute();
    }
}
