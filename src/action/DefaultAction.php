<?php
declare(strict_types=1);

namespace netvod\action;

use netvod\renderer\Layout;
use netvod\repository\ConnectionFactory;
use netvod\repository\FavoriRepository;
use netvod\repository\SerieRepository;

class DefaultAction
{
    private function pdo()
    {
        // Compat cours : certaines bases ont getConnection(), d’autres makeConnection()
        if (method_exists(ConnectionFactory::class, 'getConnection')) {
            return ConnectionFactory::getConnection();
        }
        return ConnectionFactory::makeConnection();
    }

    public function execute(): string
    {
        ConnectionFactory::setConfig(__DIR__ . '/../../config/db.config.ini');
        if (session_status() === PHP_SESSION_NONE) session_start();

        $content = '';

        /* ------------------------------ Hero ------------------------------ */
        $content .= <<<HTML
<div class="hero">
    <h1>Bienvenue sur NETVOD</h1>
    <p>Découvrez des séries passionnantes et profitez d'une expérience de streaming unique</p>
    <a href="index.php?action=catalogue" class="btn">Découvrir le catalogue</a>
</div>
HTML;

        /* ------------------------------ Mes favoris ------------------------------ */
        if (isset($_SESSION['user_id'])) {
            $userId = (int) $_SESSION['user_id'];

            $favoriRepo  = new FavoriRepository();
            $favoriteIds = $favoriRepo->getUserFavoriteIds($userId);

            if (!empty($favoriteIds)) {
                $serieRepo = new SerieRepository();
                $nbFavoris = count($favoriteIds);

                $content .= "<div class='favorites-section'>
                                <h2>Mes Favoris ({$nbFavoris})</h2>
                                <div class='series-grid'>";
                foreach ($favoriteIds as $serieId) {
                    $serie = $serieRepo->findById($serieId);
                    if (!$serie) continue;

                    $titre   = htmlspecialchars($serie->titre);
                    $desc    = htmlspecialchars($serie->descriptif ?? 'Pas de description');
                    $annee   = $serie->annee ?? 'N/A';
                    $imgName = $serie->img ?? 'default.jpg';
                    $imgPath = 'images/' . $imgName;

                    $content .= "
                    <div class='card serie-card' onclick=\"window.location.href='index.php?action=serie&id={$serie->id}'\" style='cursor:pointer;'>
                        <div class='serie-poster'>
                            <img src='{$imgPath}' alt='{$titre}' onerror=\"this.src='images/default.jpg'\">
                            <button class='favori-btn active' data-serie-id='{$serie->id}' title='Retirer des favoris'>
                                <span class='heart-icon'>❤️</span>
                            </button>
                        </div>
                        <div class='serie-info'>
                            <h3><a href='index.php?action=serie&id={$serie->id}'>{$titre}</a></h3>
                            <p>{$desc}</p>
                            <small>Année : {$annee}</small>
                        </div>
                    </div>";
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

        /* ------------------------------ Reprendre (affichage LISTE) ------------------------------ */
        if (isset($_SESSION['user_id'])) {
            $pdo    = $this->pdo();
            $userId = (int) $_SESSION['user_id'];

            // On récupère la série + le dernier épisode + la position enregistrée
            $sql = "
                SELECT
                  s.id              AS id_serie,
                  s.titre           AS serie_titre,
                  s.img             AS serie_img,
                  s.annee           AS serie_annee,
                  e.id              AS ep_id,
                  e.titre           AS ep_titre,
                  e.numero          AS ep_numero,
                  COALESCE(ev.position_sec, 0) AS position_sec
                FROM progress p
                JOIN serie   s ON s.id = p.id_serie
                JOIN episode e ON e.id = p.last_episode_id
                LEFT JOIN episode_vue ev
                       ON ev.id_user = :u AND ev.id_episode = e.id
                LEFT JOIN already_watched aw
                       ON aw.id_user = :u AND aw.id_serie = s.id
                WHERE p.id_user = :u
                  AND aw.id_user IS NULL
                ORDER BY p.updated_at DESC
            ";
            $st = $pdo->prepare($sql);
            $st->execute([':u' => $userId]);
            $rows = $st->fetchAll(\PDO::FETCH_ASSOC);

            if ($rows) {
                $content .= "<section class='favorites-section' style='margin-top:40px'>
                                <h2>Reprendre la lecture</h2>
                                <div class='resume-list'>";

                foreach ($rows as $r) {
                    $sid   = (int) $r['id_serie'];
                    $stitle= htmlspecialchars($r['serie_titre'] ?? 'Sans titre', ENT_QUOTES, 'UTF-8');
                    $simg  = 'images/' . ($r['serie_img'] ?: 'default.jpg');
                    $sannee= htmlspecialchars((string)($r['serie_annee'] ?? 'N/A'), ENT_QUOTES, 'UTF-8');

                    $epId  = (int) $r['ep_id'];
                    $etitle= htmlspecialchars($r['ep_titre'] ?? 'Épisode', ENT_QUOTES, 'UTF-8');
                    $enum  = htmlspecialchars((string)($r['ep_numero'] ?? '?'), ENT_QUOTES, 'UTF-8');

                    $pos   = (int) ($r['position_sec'] ?? 0);
                    $mm = floor($pos / 60);
                    $ss = $pos % 60;
                    $posTxt = sprintf('%02d:%02d', $mm, $ss);

                    $content .= "
                    <div class='resume-item' style='display:flex;gap:16px;align-items:center;margin:12px 0;'>
                        <img src='{$simg}' alt='{$stitle}' style='width:80px;height:120px;object-fit:cover;border-radius:6px'
                             onerror=\"this.src='images/default.jpg'\">
                        <div style='flex:1'>
                            <h3 style='margin:0'>{$stitle}</h3>
                            <p style='margin:6px 0 8px'>Épisode {$enum} : {$etitle}</p>
                            <small>Dernière position : {$posTxt}</small>
                        </div>
                        <div>
                            <a class='btn btn-play' href='index.php?action=episode&id={$epId}'>▶ Reprendre</a>
                            <a class='btn btn-secondary' href='index.php?action=serie&id={$sid}' style='margin-left:8px'>Voir la série</a>
                        </div>
                    </div>";
                }

                $content .= "</div></section>";
            }
        }

        /* ------------------------------ Déjà visionnées ------------------------------ */
        if (isset($_SESSION['user_id'])) {
            $pdo    = $this->pdo();
            $userId = (int) $_SESSION['user_id'];

            $sql = "
                SELECT s.id, s.titre, s.descriptif, s.img, s.annee
                FROM already_watched aw
                JOIN serie s ON s.id = aw.id_serie
                WHERE aw.id_user = :u
                ORDER BY aw.marked_at DESC";
            $st = $pdo->prepare($sql);
            $st->execute([':u' => $userId]);
            $rows = $st->fetchAll(\PDO::FETCH_ASSOC);

            if ($rows) {
                $content .= "<div class='favorites-section'>
                                <h2>Déjà visionnées</h2>
                                <div class='series-grid'>";
                foreach ($rows as $r) {
                    $titre = htmlspecialchars($r['titre']);
                    $desc  = htmlspecialchars($r['descriptif'] ?? 'Pas de description');
                    $img   = htmlspecialchars($r['img'] ?: 'default.jpg');
                    $annee = htmlspecialchars((string)($r['annee'] ?? 'N/A'));

                    $content .= "
                    <div class='card serie-card' onclick=\"window.location.href='index.php?action=serie&id={$r['id']}'\" style='cursor:pointer;'>
                        <div class='serie-poster'>
                            <img src='images/{$img}' alt='{$titre}' onerror=\"this.src='images/default.jpg'\">
                        </div>
                        <div class='serie-info'>
                            <h3>{$titre}</h3>
                            <p>{$desc}</p>
                            <small>Année : {$annee}</small>
                        </div>
                    </div>";
                }
                $content .= "</div></div>";
            }
        }

        return Layout::render($content, "NETVOD - Accueil");
    }
}
