<?php
class DB {
    private static ?PDO $pdo = null;

    public static function connect(): PDO {
        if (self::$pdo) return self::$pdo;

        $dsn  = 'mysql:host=127.0.0.1;dbname=serviceoficial_db;charset=utf8mb4';
        $user = 'root';
        $pass = '';

        try {
            self::$pdo = new PDO($dsn, $user, $pass, [
                PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            ]);
            return self::$pdo;
        } catch (PDOException $e) {
            http_response_code(500);
            die('Error de conexión a la base de datos: ' . $e->getMessage());
        }
    }
}