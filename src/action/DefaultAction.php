<?php
namespace netvod\action;

use netvod\renderer\Layout;
use netvod\repository\ConnectionFactory;
use netvod\repository\FavoriRepository;
use netvod\repository\SerieRepository;

class DefaultAction
{
    public function execute(): string
    {
        ConnectionFactory::setConfig(__DIR__ . '/../../config/db.config.ini');
        
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        
        $content = '';
        
        // Hero section
        $content .= <<<HTML
<div class="hero">
    <h1>Bienvenue sur NETVOD</h1>
    <p>Découvrez des séries passionnantes et profitez d'une expérience de streaming unique</p>
    <a href="index.php?action=catalogue" class="btn">Découvrir le catalogue</a>
</div>
HTML;

        // Section "Mes Favoris" si l'utilisateur est connecté
        if (isset($_SESSION['user_id'])) {
            $userId = (int)$_SESSION['user_id'];
            $favoriRepo = new FavoriRepository();
            $favoriteIds = $favoriRepo->getUserFavoriteIds($userId);
            
            if (!empty($favoriteIds)) {
                $serieRepo = new SerieRepository();
                $nbFavoris = count($favoriteIds);
                
                $content .= "<div class='favorites-section'>
                                <h2>Mes Favoris ({$nbFavoris})</h2>
                                <div class='series-grid'>";
                
                foreach ($favoriteIds as $serieId) {
                    $serie = $serieRepo->findById($serieId);
                    if ($serie) {
                        $titre = htmlspecialchars($serie->titre);
                        $descriptif = htmlspecialchars($serie->descriptif ?? 'Pas de description');
                        $annee = $serie->annee ?? 'N/A';
                        $imgName = $serie->img ?? 'default.jpg';
                        $imgPath = 'images/' . $imgName;
                        
                        $content .= "<div class='card serie-card'>
                                        <div class='serie-poster'>
                                            <img src='{$imgPath}' alt='{$titre}' onerror=\"this.src='images/default.jpg'\">
                                            <button class='favori-btn active' data-serie-id='{$serie->id}' title='Retirer des favoris'>
                                                <span class='heart-icon'>❤️</span>
                                            </button>
                                        </div>
                                        <div class='serie-info'>
                                            <h3><a href='index.php?action=serie&id={$serie->id}'>{$titre}</a></h3>
                                            <p>{$descriptif}</p>
                                            <small>Année : {$annee}</small>
                                        </div>
                                     </div>";
                    }
                }
                
                $content .= "</div></div>";
            } else {
                $content .= "<div class='favorites-section'>
                                <h2>Mes Favoris</h2>
                                <div class='no-favorites-home'>
                                    <p>Vous n'avez pas encore ajouté de séries à vos favoris.</p>
                                    <a href='index.php?action=catalogue' class='btn'>Parcourir le catalogue</a>
                                </div>
                             </div>";
            }
        }

        return Layout::render($content, "NETVOD - Accueil");
    }
}