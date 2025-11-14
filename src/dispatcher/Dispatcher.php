<?php
namespace netvod\dispatcher;

use netvod\action\CatalogueAction;
use netvod\action\CommentsAction;
use netvod\action\DefaultAction;
use netvod\action\EpisodeAction;
use netvod\action\FavorisAction;
use netvod\action\ForgotPasswordAction;
use netvod\action\LoginAction;
use netvod\action\LogoutAction;
use netvod\action\ProfileAction;
use netvod\action\RegisterAction;
use netvod\action\ResetPasswordAction;
use netvod\action\ResumeAction;
use netvod\action\SerieAction;
use netvod\action\ToggleFavoriAction;
use netvod\auth\AuthnProvided;
use netvod\auth\Authz;

ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

class Dispatcher
{
    public function run(): void
    {
        $actionName = strtolower($_GET['action'] ?? 'default');

        // Vérifier l'authentification et les droits
        if (!AuthnProvided::isAuthenticated() && !Authz::isAllowed($actionName)) {
            if (session_status() === PHP_SESSION_NONE) {
                session_start();
            }
            $_SESSION['flash_error'] = 'Veuillez vous connecter pour continuer';
            header('Location: index.php?action=login');
            exit;
        }

        switch ($actionName) {
            case 'catalogue':
                $action = new CatalogueAction();
                break;
            case 'serie':
                $action = new SerieAction();
                break;
            case 'episode':
                $action = new EpisodeAction();
                break;
            case 'login':
                $action = new LoginAction();
                break;
            case 'logout':
                $action = new LogoutAction();
                break;
            case 'register':
                $action = new RegisterAction();
                break;
            case 'profile':
                $action = new ProfileAction();
                break;
            case 'forgotpassword':
                $action = new ForgotPasswordAction();
                break;
            case 'resetpassword':
                $action = new ResetPasswordAction();
                break;
            case 'favoris':
                $action = new FavorisAction();
                break;
            case 'resume':
                $action = new ResumeAction();
                break;
            case 'comments':
                $action = new CommentsAction();
                break;
            case 'togglefavori':
                (new ToggleFavoriAction())->execute();
                return;
            case 'default':
            default:
                $action = new DefaultAction();
                break;
        }

        $result = $action->execute();

        if (is_string($result)) {
            $html = trim($result);
            if ($html !== '' && (str_starts_with($html, '<!DOCTYPE') || str_starts_with($html, '<html'))) {
                echo $html;
                return;
            }
            $title = $this->extractTitle($action) ?? $this->defaultTitle($actionName);
            $this->renderPage($html, $title);
            return;
        }

        $this->renderPage('', $this->defaultTitle($actionName));
    }

    private function extractTitle(object $action): ?string
    {
        if (method_exists($action, 'getTitle')) {
            $t = $action->getTitle();
            if (is_string($t) && $t !== '') return $t;
        }
        if (property_exists($action, 'title')) {
            $t = $action->title;
            if (is_string($t) && $t !== '') return $t;
        }
        return null;
    }

    private function renderPage(string $content, string $title = 'NETVOD'): void
    {
        $cssPath = __DIR__ . '/../assets/style.css';
        $styles = file_exists($cssPath) ? (string) file_get_contents($cssPath) : '';

        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }

        http_response_code(200);

        echo "<!DOCTYPE html>\n<html lang='fr'>\n<head>\n  <meta charset='UTF-8'>\n  <meta name='viewport' content='width=device-width, initial-scale=1.0'>\n  <title>" . htmlspecialchars($title) . "</title>\n  <style>{$styles}</style>\n</head>\n<body>\n  <nav>\n    <div class='container'>\n      <a href='index.php'>NETVOD</a>\n      <a href='index.php'>Accueil</a>\n      <a href='index.php?action=catalogue'>Catalogue</a>\n      " . $this->getUserMenu() . "\n    </div>\n  </nav>\n\n  <div class='container'>\n    " . $this->renderFlash() . "\n    {$content}\n  </div>\n\n  <footer>\n    <p>&copy; 2025 NETVOD - Romain, Stéphane, Eliot, Matteo</p>\n  </footer>\n\n  " . $this->getJavaScript() . "\n</body>\n</html>";
    }

    private function renderFlash(): string
    {
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

    private function getJavaScript(): string
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

    private function getUserMenu(): string
    {
        if (isset($_SESSION['user_id'])) {
            return "<a href='index.php?action=profile'>Profil</a>";
        }
        return "<a href='index.php?action=login'>Connexion</a>";
    }

    private function defaultTitle(string $action): string
    {
        return match ($action) {
            'catalogue' => 'Catalogue - NETVOD',
            'login' => 'Connexion - NETVOD',
            'register' => 'Inscription - NETVOD',
            'profile' => 'Mon profil - NETVOD',
            'favoris' => 'Mes Favoris - NETVOD',
            'resume' => 'Reprendre - NETVOD',
            'comments' => 'Commentaires - NETVOD',
            default => 'NETVOD',
        };
    }
}
