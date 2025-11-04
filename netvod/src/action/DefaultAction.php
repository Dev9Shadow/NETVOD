<?php
namespace netvod\action;

class DefaultAction
{
    public function execute(): string
    {
        ob_start();
        include __DIR__ . '/../../templates/default.php';
        return ob_get_clean();
    }
}
