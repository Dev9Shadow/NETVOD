<?php
namespace netvod\auth;

class Authz
{
    public static function isAllowed(string $actionName): bool
    {
        $action = strtolower($actionName);
        $public = [
            'default',
            'login',
            'register',
            'catalogue',
            'serie',
            'logout',
            'forgotpassword',
            'resetpassword',
        ];
        return in_array($action, $public, true);
    }
}

