<?php
declare(strict_types=1);

namespace netvod\renderer;

class Layout
{
    public static function render(string $content, string $title = "NETVOD"): string
    {
        $cssPath = __DIR__ . '/../assets/style.css';
        $styles = file_exists($cssPath) ? file_get_contents($cssPath) : '';
        if ($styles === '') {
            error_log("CSS file not found at: " . $cssPath);
        }

        return "<!DOCTYPE html>
<html lang='fr'>
<head>
  <meta charset='UTF-8'>
  <meta name='viewport' content='width=device-width, initial-scale=1.0'>
  <title>{$title}</title>
  <style>{$styles}</style>
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
    " . self::renderFlash() . "
    {$content}
  </div>

  <footer>
    <p>&copy; 2025 NETVOD - Romain, Stéphane, Eliot, Matteo</p>
  </footer>

  " . self::getJavaScript() . "
</body>
</html>";
    }

    private static function renderFlash(): string
    {
        if (session_status() === PHP_SESSION_NONE) session_start();

        $html = '';
        if (!empty($_SESSION['flash_error'])) {
            $msg = htmlspecialchars((string)$_SESSION['flash_error']);
            unset($_SESSION['flash_error']);
            $html .= "<div class='error'>{$msg}</div>";
        }
        if (!empty($_SESSION['flash_success'])) {
            $msg = htmlspecialchars((string)$_SESSION['flash_success']);
            unset($_SESSION['flash_success']);
            $html .= "<div class='success'>{$msg}</div>";
        }
        return $html;
    }

    private static function getJavaScript(): string
    {
        return <<<'JS'
<script>
// Gestion des favoris (cœur)
document.addEventListener('DOMContentLoaded', function() {
  const favoriBtns = document.querySelectorAll('.favori-btn');
  favoriBtns.forEach(btn => {
    btn.addEventListener('click', async function(e) {
      e.preventDefault();
      e.stopPropagation();

      const serieId = this.dataset.serieId;
      const heartIcon = this.querySelector('.heart-icon');
      const card = this.closest('.serie-card');

      try {
        const res = await fetch('index.php?action=toggleFavori', {
          method: 'POST',
          headers: {'Content-Type': 'application/x-www-form-urlencoded'},
          body: 'serie_id=' + encodeURIComponent(serieId)
        });
        const data = await res.json();

        if (data.success) {
          if (data.isFavorite) {
            this.classList.add('active');
            heartIcon.textContent = '❤️';
            this.title = 'Retirer des favoris';
          } else {
            this.classList.remove('active');
            heartIcon.textContent = '🤍';
            this.title = 'Ajouter aux favoris';

            const isHome = location.search === '' || location.search === '?action=default';
            const isFavoris = location.search.includes('action=favoris');
            if (isHome || isFavoris) {
              card.style.transition = 'all .3s ease';
              card.style.opacity = '0';
              card.style.transform = 'scale(.8)';
              setTimeout(() => {
                card.remove();
                const remaining = document.querySelectorAll('.favorites-section .serie-card');
                if (remaining.length === 0) location.reload();
              }, 300);
            }
          }
        } else {
          alert(data.message || 'Une erreur est survenue');
        }
      } catch (err) {
        console.error(err);
        alert('Une erreur est survenue');
      }
    });
  });
});
</script>
JS;
    }

    private static function getUserMenu(): string
    {
        if (session_status() === PHP_SESSION_NONE) session_start();

        if (isset($_SESSION['user_id'])) {
            return "<a href='index.php?action=profile'>Profil</a>";
        }
        return "<a href='index.php?action=login'>Connexion</a>";
    }
}
