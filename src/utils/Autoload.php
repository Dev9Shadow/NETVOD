<?php
namespace netvod\utils;

class Autoload
{
    public static function register(): void
    {
        spl_autoload_register(function ($class) {
            $class = str_replace('netvod\\', '', $class);
            $path = __DIR__ . '/../' . str_replace('\\', '/', $class) . '.php';
            if (file_exists($path)) {
                require_once $path;
            }
        });
    }
}
