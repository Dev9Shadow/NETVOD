<?php
namespace netvod\action;

use netvod\repository\ConnectionFactory;
use netvod\repository\SerieRepository;
use netvod\repository\FavoriRepository;
use netvod\repository\PublicCibleRepository;
use netvod\repository\CommentRepository;
 

class CatalogueAction
{
    public string $title = '';
    public function execute(): string
    {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }

        ConnectionFactory::setConfig(__DIR__ . '/../../config/db.config.ini');

        $serieRepo = new SerieRepository();
        $publicRepo = new PublicCibleRepository();
        $commentRepo = new CommentRepository();

        $searchQuery = trim($_GET['search'] ?? '');
        $selectedGenre = $_GET['genre'] ?? '';
        $selectedPublic = isset($_GET['public']) && $_GET['public'] !== '' ? (int)$_GET['public'] : null;
        $sort = $_GET['sort'] ?? 'titre_asc';
        $sortNote = $_GET['sort_note'] ?? '';

        $genresList = $serieRepo->getDistinctGenres();
        $publics = $publicRepo->findAll();

        $series = $serieRepo->findAllFiltered(
            $selectedGenre !== '' ? $selectedGenre : null,
            $selectedPublic,
            $searchQuery
        );
        $isSearching = $searchQuery !== '';
        // Récupérer les IDs des favoris de l'utilisateur connecté
        $userFavorites = [];
        if (isset($_SESSION['user_id'])) {
            $favoriRepo = new FavoriRepository();
            $userFavorites = $favoriRepo->getUserFavoriteIds((int)$_SESSION['user_id']);
        }

        $episodeCounts = [];
        $avgNotes = [];
        if (strpos($sort, 'episodes') !== false) {
            foreach ($series as $ser) {
                $episodeCounts[$ser->id] = $serieRepo->countEpisodes($ser->id);
            }
        }
        if (in_array($sortNote, ['note_desc', 'note_asc'], true)) {
            foreach ($series as $ser) {
                $avgNotes[$ser->id] = $commentRepo->getAverageNote($ser->id) ?? 0.0;
            }
        }

        // Trie
        if ($sortNote === 'note_desc' || $sortNote === 'note_asc') {
            usort($series, function ($a, $b) use ($avgNotes, $sortNote) {
                $na = $avgNotes[$a->id] ?? 0.0;
                $nb = $avgNotes[$b->id] ?? 0.0;
                if ($na === $nb) return 0;
                $cmp = $na <=> $nb;
                return $sortNote === 'note_desc' ? -$cmp : $cmp;
            });
        } else {
            switch ($sort) {
                case 'titre_desc':
                    usort($series, fn($a, $b) => strcasecmp($b->titre, $a->titre));
                    break;
                case 'date_desc':
                    usort($series, function ($a, $b) {
                        $ta = $a->date_ajout ? strtotime($a->date_ajout) : 0;
                        $tb = $b->date_ajout ? strtotime($b->date_ajout) : 0;
                        return $tb <=> $ta;
                    });
                    break;
                case 'date_asc':
                    usort($series, function ($a, $b) {
                        $ta = $a->date_ajout ? strtotime($a->date_ajout) : 0;
                        $tb = $b->date_ajout ? strtotime($b->date_ajout) : 0;
                        return $ta <=> $tb;
                    });
                    break;
                case 'episodes_desc':
                    usort($series, function ($a, $b) use ($episodeCounts, $serieRepo) {
                        $ea = $episodeCounts[$a->id] ?? $serieRepo->countEpisodes($a->id);
                        $eb = $episodeCounts[$b->id] ?? $serieRepo->countEpisodes($b->id);
                        return $eb <=> $ea;
                    });
                    break;
                case 'episodes_asc':
                    usort($series, function ($a, $b) use ($episodeCounts, $serieRepo) {
                        $ea = $episodeCounts[$a->id] ?? $serieRepo->countEpisodes($a->id);
                        $eb = $episodeCounts[$b->id] ?? $serieRepo->countEpisodes($b->id);
                        return $ea <=> $eb;
                    });
                    break;
                case 'titre_asc':
                default:
                    usort($series, fn($a, $b) => strcasecmp($a->titre, $b->titre));
                    break;
            }
        }

        // Vue
        $html = "<h1>Catalogue des series</h1>";

        $searchValue = htmlspecialchars($searchQuery);
        $html .= "<div class='search-container'>
                    <form method='GET' action='index.php' class='search-form'>
                        <input type='hidden' name='action' value='catalogue'>
                        <div class='search-input-wrapper'>
                            <input type='text' 
                                   name='search' 
                                   placeholder='Rechercher une serie par titre ou description...' 
                                   value='{$searchValue}'
                                   class='search-input'>
                            <button type='submit' class='search-btn'>Rechercher</button>";

        if ($isSearching) {
            $html .= "<a href='index.php?action=catalogue' class='clear-search-btn' title='Effacer la recherche'>&times;</a>";
        }

        $html .= "      </div>";

        $html .= "<div class='catalogue-controls'>";

        $html .= "<div class='filters-left'>";
        // Genre
        $html .= "<select name='genre' aria-label='Filtrer par genre'>";
        $html .= "<option value=''" . ($selectedGenre === '' ? " selected" : "") . ">Tous les genres</option>";
        foreach ($genresList as $g) {
            $gEsc = htmlspecialchars($g);
            $sel = ($selectedGenre === $g) ? " selected" : "";
            $html .= "<option value='{$gEsc}'{$sel}>{$gEsc}</option>";
        }
        $html .= "</select>";
        // Public
        $html .= "<select name='public' aria-label='Filtrer par public'>";
        $html .= "<option value=''" . ($selectedPublic === null ? " selected" : "") . ">Tous publics</option>";
        foreach ($publics as $p) {
            $sel = ($selectedPublic !== null && $selectedPublic === (int)$p->id) ? " selected" : "";
            $nom = htmlspecialchars($p->nom);
            $html .= "<option value='{$p->id}'{$sel}>{$nom}</option>";
        }
        $html .= "</select>";
        $html .= "</div>";

        $html .= "<div class='sort-middle'>";
        $html .= "<select name='sort' aria-label='Trier le catalogue'>";
        $html .= "<option value='titre_asc'" . ($sort === 'titre_asc' ? " selected" : "") . ">Titre A-Z</option>";
        $html .= "<option value='titre_desc'" . ($sort === 'titre_desc' ? " selected" : "") . ">Titre Z-A</option>";
        $html .= "<option value='date_desc'" . ($sort === 'date_desc' ? " selected" : "") . ">Date recente</option>";
        $html .= "<option value='date_asc'" . ($sort === 'date_asc' ? " selected" : "") . ">Date ancienne</option>";
        $html .= "<option value='episodes_desc'" . ($sort === 'episodes_desc' ? " selected" : "") . ">Nb episodes eleve</option>";
        $html .= "<option value='episodes_asc'" . ($sort === 'episodes_asc' ? " selected" : "") . ">Nb episodes faible</option>";
        $html .= "</select>";
        $html .= "</div>";

        $html .= "<div class='rating-right'>";
        $html .= "<select name='sort_note' aria-label='Trier par note moyenne'>";
        $html .= "<option value=''" . ($sortNote === '' ? " selected" : "") . ">Ne pas trier par note</option>";
        $html .= "<option value='note_desc'" . ($sortNote === 'note_desc' ? " selected" : "") . ">Note decroissante</option>";
        $html .= "<option value='note_asc'" . ($sortNote === 'note_asc' ? " selected" : "") . ">Note croissante</option>";
        $html .= "</select>";
        $html .= "</div>";

        $html .= "<button type='submit' class='search-btn'>Appliquer</button>";

        $html .= "</div>"; 

        $html .= "    </form>
                  </div>";

        // Résultats de recherche
        if ($isSearching) {
            $nbResults = count($series);
            $plural = $nbResults > 1 ? 's' : '';
            $html .= "<p class='search-results-info'>
                        <strong>{$nbResults}</strong> resultat{$plural} pour &laquo; <em>{$searchValue}</em> &raquo;
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

        $this->title = "Catalogue - NETVOD";
        return $html;
    }
}
