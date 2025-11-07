<?php
namespace netvod\action;

use netvod\repository\ConnectionFactory;
use netvod\repository\FavoriRepository;

class ToggleFavoriAction
{
    public function execute(): string
    {
        // Vérifier que l'utilisateur est connecté
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        
        if (!isset($_SESSION['user_id'])) {
            header('Content-Type: application/json');
            echo json_encode(['success' => false, 'message' => 'Non connecté']);
            exit;
        }

        ConnectionFactory::setConfig(__DIR__ . '/../../config/db.config.ini');

        $serieId = (int)($_POST['serie_id'] ?? 0);
        $userId = (int)$_SESSION['user_id'];

        if ($serieId <= 0) {
            header('Content-Type: application/json');
            echo json_encode(['success' => false, 'message' => 'ID série invalide']);
            exit;
        }

        $repo = new FavoriRepository();
        $isFavorite = $repo->isFavorite($userId, $serieId);

        if ($isFavorite) {
            // Retirer des favoris
            $success = $repo->remove($userId, $serieId);
            $action = 'removed';
        } else {
            // Ajouter aux favoris
            $success = $repo->add($userId, $serieId);
            $action = 'added';
        }

        header('Content-Type: application/json');
        echo json_encode([
            'success' => $success,
            'action' => $action,
            'isFavorite' => !$isFavorite
        ]);
        exit;
    }
}