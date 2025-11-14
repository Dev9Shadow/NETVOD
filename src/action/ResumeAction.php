<?php
declare(strict_types=1);

namespace netvod\action;

use netvod\repository\ConnectionFactory;
use netvod\repository\ProgressRepository;
 

class ResumeAction
{
    public string $title = '';
    public function execute(): string
    {
        ConnectionFactory::setConfig(__DIR__ . '/../../config/db.config.ini');
        if (session_status() === PHP_SESSION_NONE) session_start();

        if (!isset($_SESSION['user_id'])) {
            $this->title = "Reprendre - NETVOD";
            return "<h1>Reprendre</h1>
                        <p>Vous devez être connecté pour voir vos séries en cours.</p>
                        <p><a class='btn' href='index.php?action=login'>Se connecter</a></p>";
        }

        $repo  = new ProgressRepository();
        $rows  = $repo->listForUser((int)$_SESSION['user_id']);

        $html  = "<h1>Reprendre</h1>";

        if (empty($rows)) {
            $html .= "<p>Aucune série en cours pour l’instant.</p>
                      <p><a class='btn' href='index.php?action=catalogue'>Aller au catalogue</a></p>";
            $this->title = "Reprendre - NETVOD";
            return $html;
        }

        $html .= "<div class='resume-list'>";
        foreach ($rows as $r) {
            $serieId   = (int)$r['id_serie'];
            $serieTit  = htmlspecialchars($r['serie_titre'] ?? 'Sans titre');
            $serieImg  = $r['serie_img'] ?: 'default.jpg';
            $imgPath   = 'images/' . $serieImg;

            $epId      = (int)($r['ep_id'] ?? 0);
            $epTit     = htmlspecialchars($r['ep_titre'] ?? 'Épisode');
            $epNum     = htmlspecialchars((string)($r['ep_numero'] ?? '?'));
            $posSec    = (int)($r['position_sec'] ?? 0);

            $mm = floor($posSec / 60);
            $ss = $posSec % 60;
            $posTxt = sprintf('%02d:%02d', $mm, $ss);

            $html .= "<div class='resume-item' style='display:flex;gap:16px;align-items:center;margin:12px 0;'>
                        <img src='{$imgPath}' alt='{$serieTit}' style='width:80px;height:120px;object-fit:cover;border-radius:6px'
                             onerror=\"this.src='images/default.jpg'\">
                        <div style='flex:1'>
                            <h3 style='margin:0'>{$serieTit}</h3>
                            <p style='margin:6px 0 8px'>Épisode {$epNum} : {$epTit}</p>
                            <small>Dernière position : {$posTxt}</small>
                        </div>
                        <div>
                            <a class='btn btn-play' href='index.php?action=episode&id={$epId}'>▶ Reprendre</a>
                            <a class='btn btn-secondary' href='index.php?action=serie&id={$serieId}' style='margin-left:8px'>Voir la série</a>
                        </div>
                      </div>";
        }
        $html .= "</div>";

        $this->title = "Reprendre - NETVOD";
        return $html;
    }
}
