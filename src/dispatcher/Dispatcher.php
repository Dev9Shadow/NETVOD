<?php
namespace netvod\dispatcher;

use netvod\action\DefaultAction;
use netvod\auth\AuthnProvided;


ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);

error_reporting(E_ALL);

class Dispatcher
{
    public function run(): void
    {
        $actionName = $_GET['action'] ?? 'default';
        $actionClass = 'netvod\\action\\' . ucfirst($actionName) . 'Action';

        if (!AuthnProvided::isAuthenticated()) {
            if ($actionName !== 'login' && $actionName !== 'register') {
                header('Location: index.php?action=login');
                return;
            }
        }

        if (!class_exists($actionClass)) {
            $actionClass = DefaultAction::class;
        }

        $action = new $actionClass();
        echo $action->execute();
    }
}
