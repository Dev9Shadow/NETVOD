<?php
declare(strict_types=1);

namespace netvod\renderer;

use netvod\entity\Episode;

class EpisodeRenderer
{
    public static function renderList(array $episodes): string
    {
        if (empty($episodes)) return "<p>Aucun épisode disponible pour cette série.</p>";
        $html = "<div class='episode-list'>";
        foreach ($episodes as $ep) $html .= self::renderItem($ep);
        return $html . "</div>";
    }

    public static function renderItem(Episode $episode): string
    {
        $id     = htmlspecialchars((string)($episode->id ?? ''));
        $titre  = htmlspecialchars($episode->titre ?? 'Sans titre');
        $numero = htmlspecialchars((string)($episode->numero ?? 'N/A'));
        $duree  = htmlspecialchars((string)($episode->duree ?? 0));
        $resume = htmlspecialchars($episode->resume ?? 'Pas de résumé disponible');
        if (strlen($resume) > 200) $resume = substr($resume, 0, 200) . '...';

        return <<<HTML
        <div class='episode-item'>
            <h4>Épisode {$numero} : {$titre}</h4>
            <p><small>Durée : {$duree} min</small></p>
            <p>{$resume}</p>
            <a href='index.php?action=episode&id={$id}'>Regarder →</a>
        </div>
HTML;
    }

    public static function renderDetail(Episode $episode, int $resumeSec = 0, bool $logged = false): string
    {
        $titre     = htmlspecialchars($episode->titre ?? 'Sans titre');
        $numero    = htmlspecialchars((string)($episode->numero ?? 'N/A'));
        $duree     = htmlspecialchars((string)($episode->duree ?? 0));
        $resume    = htmlspecialchars($episode->resume ?? 'Pas de résumé disponible');
        $videoPath = $episode->file ?? '';
        $serieId   = (string)($episode->id_serie ?? '');
        $epId      = (int)($episode->id ?? 0);

        $videoHtml = '';
        if (!empty($videoPath)) {
            $videoPathSafe = htmlspecialchars($videoPath);
            $dataLogged    = $logged ? '1' : '0';
            $postUrl       = "index.php?action=episode&id={$epId}";
            $videoHtml = <<<HTML
            <div style='margin: 30px 0;'>
                <video id="player"
                       controls
                       width="100%"
                       style="max-width: 800px; border-radius: 8px;"
                       data-episode="{$epId}"
                       data-resume="{$resumeSec}"
                       data-logged="{$dataLogged}"
                       data-post-url="{$postUrl}">
                    <source src="{$videoPathSafe}" type="video/mp4">
                    Votre navigateur ne supporte pas la balise vidéo.
                </video>
            </div>
HTML;
        }

        $backLink = '';
        if (!empty($serieId)) {
            $backLink = "<a href='index.php?action=serie&id={$serieId}' style='display: inline-block; margin-bottom: 20px;'>← Retour à la série</a>";
        }

        $commentForm = '';
        if ($logged && !empty($serieId)) {
            $commentForm = <<<HTML
            <div class="comment-form" style="margin-top:28px;">
                <h2>Laisser un avis</h2>
                <form method="post" action="index.php?action=episode&id={$epId}" onsubmit="window.__nvSubmitting=true;">
                    <input type="hidden" name="do" value="add_comment">
                    <input type="hidden" name="id_serie" value="{$serieId}">
                    
                    <label for="note">Note</label><br>
                    <select name="note" id="note" required>
                        <option value="5">5</option>
                        <option value="4">4</option>
                        <option value="3">3</option>
                        <option value="2">2</option>
                        <option value="1">1</option>
                    </select>
                    <br><br>

                    <label for="contenu">Commentaire</label><br>
                    <textarea name="contenu" id="contenu" rows="4" required style="width:100%;max-width:800px;"></textarea>
                    <br><br>
                    
                    <button class="btn">Publier</button>
                </form>
            </div>
HTML;
        }

        $script = <<<JS
<script>
document.addEventListener('DOMContentLoaded', function() {
  const v = document.getElementById('player');
  if (!v) return;

  const LOGGED   = v.dataset.logged === '1';
  const EP_ID    = parseInt(v.dataset.episode || '0', 10);
  const RESUME   = parseInt(v.dataset.resume  || '0', 10);
  const POST_URL = v.dataset.postUrl;

  function postState(pos, vu) {
    if (!LOGGED || !EP_ID) return;
    if (window.__nvSubmitting) return;
    const body = new URLSearchParams({
      episode_id: EP_ID,
      position_sec: Math.max(0, Math.floor(pos || 0)),
      vu: vu ? 1 : 0
    }).toString();

    fetch(POST_URL, {
      method: 'POST',
      headers: {'Content-Type': 'application/x-www-form-urlencoded'},
      body
    }).catch(() => {});
  }

  v.addEventListener('loadedmetadata', () => {
    if (RESUME > 0 && RESUME < (v.duration || 1) - 2) {
      try { v.currentTime = RESUME; } catch(e) {}
    }
  }, { once:true });

  let lastSent = 0;
  v.addEventListener('timeupdate', () => {
    if (!LOGGED) return;
    if (v.currentTime - lastSent >= 10) {
      lastSent = v.currentTime;
      postState(v.currentTime, 0);
    }
  });

  v.addEventListener('pause',  () => postState(v.currentTime, 0));
  document.addEventListener('visibilitychange', () => {
    if (document.visibilityState === 'hidden') postState(v.currentTime, 0);
  });
  window.addEventListener('beforeunload', () => postState(v.currentTime, 0));
  v.addEventListener('ended', () => postState(v.duration || v.currentTime, 1));
});
</script>
JS;

        return <<<HTML
        <div class='episode-detail'>
            {$backLink}
            <h1>Épisode {$numero} : {$titre}</h1>
            <p><small>Durée : {$duree} secondes</small></p>
            {$videoHtml}
            <h2>Résumé</h2>
            <p style='line-height: 1.8;'>{$resume}</p>
            {$commentForm}
        </div>
        {$script}
HTML;
    }

    public static function renderNotFound(): string
    {
        return <<<HTML
        <div class='error'>
            <h2>Épisode introuvable</h2>
            <p>L'épisode que vous recherchez n'existe pas ou a été supprimé.</p>
            <a href='index.php?action=catalogue'>Retour au catalogue</a>
        </div>
HTML;
    }
}
