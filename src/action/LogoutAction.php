<?php
namespace netvod\action;

class LogoutAction
{
    public function execute(): string
    {
        session_start();
        session_destroy();
        return "<p>Vous avez été déconnecté.</p>
                <p><a href='index.php'>Retour à l'accueil</a></p>";
    }
}
