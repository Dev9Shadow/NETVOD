<?php
namespace netvod\dispatcher;

use netvod\action\DefaultAction;

class Dispatcher
{
    public function run(): void
    {
        $actionName = $_GET['action'] ?? 'default';
        $actionClass = 'netvod\\action\\' . ucfirst($actionName) . 'Action';

        if (!class_exists($actionClass)) {
            $actionClass = DefaultAction::class;
        }

        $action = new $actionClass();
        echo $action->execute();
    }
}
