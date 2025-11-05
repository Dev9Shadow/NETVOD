<?php
namespace netvod\renderer;

use netvod\entity\Serie;

class CatalogueRenderer
{
    /**
     * Affiche une grille de séries
     * @param Serie[] $series
     * @return string
     */
    public static function render(array $series): string
    {
        if (empty($series)) {
            return "<p>Aucune série disponible pour le moment.</p>";
        }

        $html = "<div class='series-grid'>";
        
        foreach ($series as $serie) {
            $html .= SerieRenderer::renderCard($serie);
        }
        
        $html .= "</div>";
        
        return $html;
    }

    /**
     * Affiche le catalogue complet avec titre
     * @param Serie[] $series
     * @return string
     */
    public static function renderComplete(array $series): string
    {
        $count = count($series);
        $html = "<h1>Catalogue des séries</h1>";
        $html .= "<p style='color: #b3b3b3; margin-bottom: 30px;'>{$count} série(s) disponible(s)</p>";
        $html .= self::render($series);
        
        return $html;
    }
}