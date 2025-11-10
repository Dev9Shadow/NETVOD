<?php
declare(strict_types=1);

namespace netvod\action;

use netvod\renderer\Layout;
use netvod\repository\ConnectionFactory;
use netvod\repository\FavoriRepository;
use netvod\repository\SerieRepository;
use netvod\repository\ProgressRepository;

class DefaultAction
{
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

        // Reprendre
        if (isset($_SESSION['user_id'])) {
            $pr = new ProgressRepository();
            $rows = $pr->listForUser((int)$_SESSION['user_id']);
            if (!empty($rows)) {
                $content .= "<section class='favorites-section' style='margin-top:40px'><h2>Reprendre</h2><div class='resume-list'>";
                foreach ($rows as $r) {
                    $sid = (int)$r['id_serie'];
                    $st  = htmlspecialchars($r['serie_titre'] ?? 'Sans titre', ENT_QUOTES, 'UTF-8');
                    $sim = $r['serie_img'] ?: 'default.jpg';
                    $img = 'images/'.$sim;

                    $epId = (int)$r['ep_id'];
                    $et   = htmlspecialchars($r['ep_titre'] ?? 'Épisode', ENT_QUOTES, 'UTF-8');
                    $enum = htmlspecialchars((string)($r['ep_numero'] ?? '?'), ENT_QUOTES, 'UTF-8');
                    $pos  = (int)($r['position_sec'] ?? 0);
                    $mm=floor($pos/60); $ss=$pos%60; $posTxt=sprintf('%02d:%02d',$mm,$ss);

                    $content .= "<div class='resume-item' style='display:flex;gap:16px;align-items:center;margin:12px 0;'>
                        <img src='{$img}' alt='{$st}' style='width:80px;height:120px;object-fit:cover;border-radius:6px' onerror=\"this.src='images/default.jpg'\">
                        <div style='flex:1'>
                          <h3 style='margin:0'>{$st}</h3>
                          <p style='margin:6px 0 8px'>Épisode {$enum} : {$et}</p>
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

        // Favoris (ton code conservé)
        if (isset($_SESSION['user_id'])) {
            $userId = (int)$_SESSION['user_id'];
            $favoriRepo = new FavoriRepository();
            $favoriteIds = $favoriRepo->getUserFavoriteIds($userId);

            if (!empty($favoriteIds)) {
                $serieRepo = new SerieRepository();
                $nbFavoris = count($favoriteIds);
                $content .= "<div class='favorites-section'><h2>Mes Favoris ({$nbFavoris})</h2><div class='series-grid'>";
                foreach ($favoriteIds as $serieId) {
                    $s = $serieRepo->findById($serieId);
                    if ($s) {
                        $t = htmlspecialchars($s->titre);
                        $d = htmlspecialchars($s->descriptif ?? 'Pas de description');
                        $a = $s->annee ?? 'N/A';
                        $img = 'images/'.($s->img ?? 'default.jpg');
                        $content .= "<div class='card serie-card' onclick=\"window.location.href='index.php?action=serie&id={$s->id}'\" style='cursor:pointer;'>
                            <div class='serie-poster'>
                              <img src='{$img}' alt='{$t}' onerror=\"this.src='images/default.jpg'\">
                              <button class='favori-btn active' data-serie-id='{$s->id}' title='Retirer des favoris'><span class='heart-icon'>❤️</span></button>
                            </div>
                            <div class='serie-info'>
                              <h3><a href='index.php?action=serie&id={$s->id}'>{$t}</a></h3>
                              <p>{$d}</p><small>Année : {$a}</small>
                            </div>
                          </div>";
                    }
                }
                $content .= "</div></div>";
            } else {
                $content .= "<div class='favorites-section'><h2>Mes Favoris</h2>
                    <div class='no-favorites-home'><p>Vous n'avez pas encore ajouté de séries à vos favoris.</p>
                    <a href='index.php?action=catalogue' class='btn'>Parcourir le catalogue</a></div></div>";
            }
        }

        return Layout::render($content, "NETVOD - Accueil");
    }
}
