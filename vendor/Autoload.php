<?php
namespace netvod;

class Autoload
{
    public static function register(): void
    {
        spl_autoload_register([__CLASS__, 'autoload']);
    }

    private static function autoload(string $className): void
    {
        // Remplacer le namespace par le chemin
        $className = str_replace('netvod\\', '', $className);
        $className = str_replace('\\', '/', $className);
        
        $file = __DIR__ . '/../src/' . $className . '.php';
        
        if (file_exists($file)) {
            require_once $file;
        }
    }
}

Autoload::register();