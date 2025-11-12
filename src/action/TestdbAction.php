<?php
namespace netvod\action;

use netvod\repository\ConnectionFactory;

class TestdbAction
{
    public string $title = '';
    public function execute(): string
    {
        $path = __DIR__ . '/../../config/db.config.ini';
        ConnectionFactory::setConfig($path);

        $pdo = ConnectionFactory::getConnection();
        $count = $pdo->query("SELECT COUNT(*) FROM serie")->fetchColumn();

        $this->title = 'Test DB - NETVOD';
        return "<h1>Test de connexion</h1>
                <p>Connexion réussie</p>
                <p>Nombre de séries dans la base : $count</p>
                <a href='index.php'>Retour</a>";
    }
}
