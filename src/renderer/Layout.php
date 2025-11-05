<?php
namespace netvod\renderer;

class Layout
{
    public static function render(string $content, string $title = "NETVOD"): string
    {
        $cssPath = __DIR__ . '/../assets/style.css';
        $styles = '';
        if (file_exists($cssPath)) {
            $styles = file_get_contents($cssPath);
        } else {
            error_log("CSS file not found at: " . $cssPath);
        }

        return "<!DOCTYPE html>
<html lang='fr'>
<head>
    <meta charset='UTF-8'>
    <meta name='viewport' content='width=device-width, initial-scale=1.0'>
    <title>{$title}</title>
    <style>
    {$styles}
    </style>
</head>
<body>
    <nav>
        <div class='container'>
            <a href='index.php'>NETVOD</a>
            <a href='index.php'>Accueil</a>
            <a href='index.php?action=catalogue'>Catalogue</a>
            " . self::getUserMenu() . "
        </div>
    </nav>
    <div class='container'>
        {$content}
    </div>
    <footer>
        <p>&copy; 2025 NETVOD - Romain, Stéphane, Eliot, Matteo</p>
    </footer>
</body>
</html>";
    }

    private static function getUserMenu(): string
    {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        if (isset($_SESSION['user_id'])) {
            return "<a href='index.php?action=profile'>Profil</a>";
        }
        return "<a href='index.php?action=login'>Connexion</a>
                <a href='index.php?action=register'>Inscription</a>";
    }
}

