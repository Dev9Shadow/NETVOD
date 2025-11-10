<?php
namespace netvod\action;

use netvod\repository\ConnectionFactory;
use netvod\repository\SerieRepository;
use netvod\repository\FavoriRepository;
use netvod\renderer\Layout;

class CatalogueAction
{
    public function execute(): string
    {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }

        ConnectionFactory::setConfig(__DIR__ . '/../../config/db.config.ini');

        $repo = new SerieRepository();
        
        // Récupérer les mots-clés de recherche
        $searchQuery = $_GET['search'] ?? '';
        $searchQuery = trim($searchQuery);
        
        // Rechercher ou afficher toutes les séries
        if (!empty($searchQuery)) {
            $series = $repo->search($searchQuery);
            $isSearching = true;
        } else {
            $series = $repo->findAll();
            $isSearching = false;
        }

        // Récupérer les IDs des favoris de l'utilisateur connecté
        $userFavorites = [];
        if (isset($_SESSION['user_id'])) {
            $favoriRepo = new FavoriRepository();
            $userFavorites = $favoriRepo->getUserFavoriteIds((int)$_SESSION['user_id']);
        }

        $html = "<h1>Catalogue des séries</h1>";
        
        // Barre de recherche
        $searchValue = htmlspecialchars($searchQuery);
        $html .= "<div class='search-container'>
                    <form method='GET' action='index.php' class='search-form'>
                        <input type='hidden' name='action' value='catalogue'>
                        <div class='search-input-wrapper'>
                            <input type='text' 
                                   name='search' 
                                   placeholder='Rechercher une série par titre ou description...' 
                                   value='{$searchValue}'
                                   class='search-input'>
                            <button type='submit' class='search-btn'>Rechercher</button>";
        
        if ($isSearching) {
            $html .= "      <a href='index.php?action=catalogue' class='clear-search-btn' title='Effacer la recherche'>✕</a>";
        }
        
        $html .= "      </div>
                    </form>
                  </div>";

        // Résultats de recherche
        if ($isSearching) {
            $nbResults = count($series);
            $plural = $nbResults > 1 ? 's' : '';
            $html .= "<p class='search-results-info'>
                        <strong>{$nbResults}</strong> résultat{$plural} pour « <em>{$searchValue}</em> »
                      </p>";
        }

        if (!empty($series)) {
            $html .= "<div class='series-grid'>";
            foreach ($series as $s) {
                $descriptif = $s->descriptif ?? 'Pas de description disponible';
                $annee = $s->annee ?? 'N/A';
                $imgName = $s->img ?? 'default.jpg';
                $imgPath = 'images/' . $imgName;
                
                // Vérifier si la série est en favori
                $isFavorite = in_array($s->id, $userFavorites);
                $favoriteClass = $isFavorite ? 'active' : '';
                $favoriteIcon = $isFavorite ? '❤️' : '🤍';
                $favoriteTitle = $isFavorite ? 'Retirer des favoris' : 'Ajouter aux favoris';
                
                $favoriBtn = '';
                if (isset($_SESSION['user_id'])) {
                    $favoriBtn = "<button class='favori-btn {$favoriteClass}' data-serie-id='{$s->id}' title='{$favoriteTitle}'>
                                    <span class='heart-icon'>{$favoriteIcon}</span>
                                  </button>";
                }
                
                $html .= "<div class='card serie-card' onclick=\"window.location.href='index.php?action=serie&id={$s->id}'\" style='cursor:pointer;'>
                            <div class='serie-poster'>
                                <img src='{$imgPath}' alt='" . htmlspecialchars($s->titre) . "' onerror=\"this.src='images/default.jpg'\">
                                {$favoriBtn}
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

        return Layout::render($html, "Catalogue - NETVOD");
    }
}
