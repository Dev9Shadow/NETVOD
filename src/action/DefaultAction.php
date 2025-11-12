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

        $content = <<<HTML
<div class="hero">
  <h1>Bienvenue sur NETVOD</h1>
  <p>Découvrez des séries passionnantes et profitez d'une expérience de streaming unique</p>
  <a href="index.php?action=catalogue" class="btn">Découvrir le catalogue</a>
</div>
HTML;

        /* ------------------------------ Reprendre ------------------------------ */
        if (isset($_SESSION['user_id'])) {
            $pdo    = $this->pdo();
            $userId = (int) $_SESSION['user_id'];

            // Séries en cours = présentes dans progress et non déjà vues
            $sql = "
                SELECT s.id              AS serie_id,
                       s.titre           AS serie_titre,
                       s.descriptif      AS serie_desc,
                       s.img             AS serie_img,
                       s.annee           AS serie_annee,
                       p.last_episode_id AS ep_id
                FROM progress p
                JOIN serie s ON s.id = p.id_serie
                LEFT JOIN already_watched aw 
                       ON aw.id_user = :u AND aw.id_serie = s.id
                WHERE p.id_user = :u AND aw.id_user IS NULL
                ORDER BY p.updated_at DESC";
            $st = $pdo->prepare($sql);
            $st->execute([':u' => $userId]);
            $rows = $st->fetchAll(\PDO::FETCH_ASSOC);

            if (!empty($rows)) {
                $content .= "<div class='favorites-section'>
                               <h2>Reprendre la lecture</h2>
                               <div class='series-grid'>";
                foreach ($rows as $r) {
                    $sid  = (int)$r['serie_id'];
                    $stt  = htmlspecialchars($r['serie_titre'] ?? 'Sans titre', ENT_QUOTES, 'UTF-8');
                    $sd   = htmlspecialchars($r['serie_desc']  ?? 'Pas de description', ENT_QUOTES, 'UTF-8');
                    $sa   = htmlspecialchars((string)($r['serie_annee'] ?? 'N/A'), ENT_QUOTES, 'UTF-8');
                    $img  = 'images/' . (($r['serie_img'] ?? '') !== '' ? $r['serie_img'] : 'default.jpg');
                    $epId = (int)$r['ep_id'];

                    $content .= "
                    <div class='card serie-card'>
                        <div class='serie-poster' onclick=\"window.location.href='index.php?action=episode&id={$epId}'\" style='cursor:pointer;'>
                            <img src='{$img}' alt='{$stt}' onerror=\"this.src='images/default.jpg'\">
                        </div>
                        <div class='serie-info'>
                            <h3>{$stt}</h3>
                            <p>{$sd}</p>
                            <div class='actions'
                                 style='display:flex;flex-direction:column;align-items:flex-start;gap:8px;margin-top:8px'>
                                <a class='btn btn-play'
                                   style='display:block;width:auto'
                                   href='index.php?action=episode&id={$epId}'>▶ Reprendre</a>
                                <a class='btn btn-secondary'
                                   style='display:block;width:auto'
                                   href='index.php?action=serie&id={$sid}'>Voir la série</a>
                            </div>
                            <small>Année : {$sa}</small>
                        </div>
                    </div>";
                }
                $content .= "</div></div>";
            }
        }

        /* ------------------------------ Mes Favoris ------------------------------ */
        if (isset($_SESSION['user_id'])) {
            $userId     = (int)$_SESSION['user_id'];
            $favoriRepo = new FavoriRepository();
            $favoriteIds = $favoriRepo->getUserFavoriteIds($userId);

            if (!empty($favoriteIds)) {
                $serieRepo = new SerieRepository();
                $nbFavoris = count($favoriteIds);

                $content .= "<div class='favorites-section'>
                               <h2>Mes Favoris ({$nbFavoris})</h2>
                               <div class='series-grid'>";
                foreach ($favoriteIds as $serieId) {
                    $s = $serieRepo->findById($serieId);
                    if (!$s) continue;

                    $t   = htmlspecialchars($s->titre, ENT_QUOTES, 'UTF-8');
                    $d   = htmlspecialchars($s->descriptif ?? 'Pas de description', ENT_QUOTES, 'UTF-8');
                    $a   = htmlspecialchars((string)($s->annee ?? 'N/A'), ENT_QUOTES, 'UTF-8');
                    $img = 'images/' . (($s->img ?? '') !== '' ? $s->img : 'default.jpg');

                    $content .= "
                    <div class='card serie-card' onclick=\"window.location.href='index.php?action=serie&id={$s->id}'\" style='cursor:pointer;'>
                        <div class='serie-poster'>
                            <img src='{$img}' alt='{$t}' onerror=\"this.src='images/default.jpg'\">
                            <button class='favori-btn active' data-serie-id='{$s->id}' title='Retirer des favoris'>
                                <span class='heart-icon'>❤️</span>
                            </button>
                        </div>
                        <div class='serie-info'>
                            <h3><a href='index.php?action=serie&id={$s->id}'>{$t}</a></h3>
                            <p>{$d}</p>
                            <small>Année : {$a}</small>
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

        /* ------------------------------ Déjà visionnées ------------------------------ */
        if (isset($_SESSION['user_id'])) {
            $pdo    = $this->pdo();
            $userId = (int) $_SESSION['user_id'];

            $sql = "SELECT s.id, s.titre, s.descriptif, s.img, s.annee
                    FROM already_watched aw
                    JOIN serie s ON s.id = aw.id_serie
                    WHERE aw.id_user = :u
                    ORDER BY aw.marked_at DESC";
            $st = $pdo->prepare($sql);
            $st->execute([':u' => $userId]);
            $rows = $st->fetchAll(\PDO::FETCH_ASSOC);

            if (!empty($rows)) {
                $content .= "<div class='favorites-section'>
                               <h2>Déjà visionnées</h2>
                               <div class='series-grid'>";
                foreach ($rows as $r) {
                    $sid = (int)$r['id'];
                    $t   = htmlspecialchars($r['titre'] ?? 'Sans titre', ENT_QUOTES, 'UTF-8');
                    $d   = htmlspecialchars($r['descriptif'] ?? 'Pas de description', ENT_QUOTES, 'UTF-8');
                    $a   = htmlspecialchars((string)($r['annee'] ?? 'N/A'), ENT_QUOTES, 'UTF-8');
                    $img = 'images/' . (($r['img'] ?? '') !== '' ? $r['img'] : 'default.jpg');

                    $content .= "
                    <div class='card serie-card' onclick=\"window.location.href='index.php?action=serie&id={$sid}'\" style='cursor:pointer;'>
                        <div class='serie-poster'>
                            <img src='{$img}' alt='{$t}' onerror=\"this.src='images/default.jpg'\">
                        </div>
                        <div class='serie-info'>
                            <h3>{$t}</h3>
                            <p>{$d}</p>
                            <small>Année : {$a}</small>
                        </div>
                    </div>";
                }
                $content .= "</div></div>";
            }
        }

        return Layout::render($content, "NETVOD - Accueil");
    }
}
