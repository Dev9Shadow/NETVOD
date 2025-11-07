<?php
namespace netvod\action;

use netvod\repository\ConnectionFactory;
use netvod\repository\FavoriRepository;
use netvod\renderer\Layout;

class FavorisAction
{
    public function execute(): string
    {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        
        if (!isset($_SESSION['user_id'])) {
            return Layout::render(
                "<h1>Accès refusé</h1>
                <p>Vous devez être connecté pour accéder à vos favoris.</p>
                <p><a href='index.php?action=login' class='btn'>Se connecter</a></p>",
                "Favoris - NETVOD"
            );
        }

        ConnectionFactory::setConfig(__DIR__ . '/../../config/db.config.ini');

        $repo = new FavoriRepository();
        $favoris = $repo->getUserFavorites((int)$_SESSION['user_id']);

        $html = "<h1>Mes Favoris</h1>";

        if (empty($favoris)) {
            $html .= "<div class='no-favorites'>
                        <p>Vous n'avez pas encore de séries favorites.</p>
                        <p>Ajoutez des séries à vos favoris depuis le catalogue !</p>
                        <a href='index.php?action=catalogue' class='btn'>Découvrir le catalogue</a>
                      </div>";
        } else {
            $html .= "<div class='series-grid'>";
            foreach ($favoris as $s) {
                $descriptif = $s->descriptif ?? 'Pas de description disponible';
                $annee = $s->annee ?? 'N/A';
                $imgName = $s->img ?? 'default.jpg';
                $imgPath = 'images/' . $imgName;
                
                $html .= "<div class='card serie-card'>
                            <div class='serie-poster'>
                                <img src='{$imgPath}' alt='" . htmlspecialchars($s->titre) . "' onerror=\"this.src='images/default.jpg'\">
                                <button class='favori-btn active' data-serie-id='{$s->id}' title='Retirer des favoris'>
                                    <span class='heart-icon'>❤️</span>
                                </button>
                            </div>
                            <div class='serie-info'>
                                <h3><a href='index.php?action=serie&id={$s->id}'>" . htmlspecialchars($s->titre) . "</a></h3>
                                <p>" . htmlspecialchars($descriptif) . "</p>
                                <small>Année : {$annee}</small>
                            </div>
                          </div>";
            }
            $html .= "</div>";
        }

        return Layout::render($html, "Mes Favoris - NETVOD");
    }
}