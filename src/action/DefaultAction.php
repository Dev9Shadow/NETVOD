<?php
namespace netvod\action;

use netvod\renderer\Layout;

class DefaultAction
{
    public function execute(): string
    {
        $content = <<<HTML
<div class="hero">
    <h1>Bienvenue sur NETVOD</h1>
    <p>Découvrez des séries passionnantes et profitez d'une expérience de streaming unique</p>
    <a href="index.php?action=catalogue" class="btn">Découvrir le catalogue</a>
</div>
HTML;

        return Layout::render($content, "NETVOD - Accueil");
    }
}