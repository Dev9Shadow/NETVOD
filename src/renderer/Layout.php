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
    " . self::getJavaScript() . "
</body>
</html>";
    }

    private static function getUserMenu(): string
    {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        if (isset($_SESSION['user_id'])) {
            return "<a href='index.php?action=favoris'>Favoris</a>
                    <a href='index.php?action=profile'>Profil</a>
                    <a href='index.php?action=logout'>Déconnexion</a>";
        }
        return "<a href='index.php?action=login'>Connexion</a>
                <a href='index.php?action=register'>Inscription</a>";
    }

    private static function getJavaScript(): string
    {
        return <<<'JS'
        <script>
        // Gestion des favoris
        document.addEventListener('DOMContentLoaded', function() {
            const favoriBtns = document.querySelectorAll('.favori-btn');
            
            favoriBtns.forEach(btn => {
                btn.addEventListener('click', async function(e) {
                    e.preventDefault();
                    e.stopPropagation();
                    
                    const serieId = this.dataset.serieId;
                    const heartIcon = this.querySelector('.heart-icon');
                    
                    try {
                        const response = await fetch('index.php?action=toggleFavori', {
                            method: 'POST',
                            headers: {
                                'Content-Type': 'application/x-www-form-urlencoded',
                            },
                            body: 'serie_id=' + serieId
                        });
                        
                        const data = await response.json();
                        
                        if (data.success) {
                            if (data.isFavorite) {
                                this.classList.add('active');
                                heartIcon.textContent = '❤️';
                                this.title = 'Retirer des favoris';
                            } else {
                                this.classList.remove('active');
                                heartIcon.textContent = '🤍';
                                this.title = 'Ajouter aux favoris';
                                
                                // Si on est sur la page favoris, retirer la carte
                                if (window.location.href.includes('action=favoris')) {
                                    this.closest('.serie-card').remove();
                                    
                                    // Vérifier s'il reste des favoris
                                    if (document.querySelectorAll('.serie-card').length === 0) {
                                        location.reload();
                                    }
                                }
                            }
                        } else {
                            alert(data.message || 'Une erreur est survenue');
                        }
                    } catch (error) {
                        console.error('Erreur:', error);
                        alert('Une erreur est survenue');
                    }
                });
            });
        });
        </script>
JS;
    }
}