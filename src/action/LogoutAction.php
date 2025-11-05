<?php
namespace netvod\action;

use netvod\renderer\Layout;

class LogoutAction
{
    public function execute(): string
    {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        session_destroy();
        
        $html = "
            <div style='text-align: center; padding: 50px;'>
                <h1>Déconnexion réussie</h1>
                <p>Vous avez été déconnecté avec succès.</p>
                <p style='margin-top: 30px;'>
                    <a href='index.php' style='display: inline-block; padding: 10px 20px; background: #e50914; color: white; text-decoration: none; border-radius: 5px;'>
                        ⬅ Retour à l'accueil
                    </a>
                </p>
            </div>
        ";
        
        return Layout::render($html, "Déconnexion - NETVOD");
    }
}