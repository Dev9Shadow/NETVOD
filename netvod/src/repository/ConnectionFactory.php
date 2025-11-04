<?php
namespace netvod\repository;

use PDO;
use PDOException;

class ConnectionFactory
{
    private static ?PDO $pdo = null;

    public static function setConfig(string $filename): void
    {
        $file = realpath($filename);
        if (!$file || !file_exists($file)) {
            throw new \Exception("Erreur de lecture du fichier de configuration");
        }

        $conf = parse_ini_file($file);
        if (!$conf) {
            throw new \Exception("Fichier de configuration illisible");
        }

        $dsn = "{$conf['driver']}:host={$conf['host']};dbname={$conf['dbname']};charset={$conf['charset']}";

        try {
            self::$pdo = new PDO($dsn, $conf['username'], $conf['password']);
            self::$pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        } catch (PDOException $e) {
            die("Erreur de connexion à la base : " . $e->getMessage());
        }
    }

    public static function getConnection(): PDO
    {
        if (is_null(self::$pdo)) {
            throw new \Exception("Connexion non initialisée");
        }
        return self::$pdo;
    }
}
