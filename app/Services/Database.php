<?php

namespace App\Services;

use PDO;
use PDOException;

class Database {
    private static ?PDO $instance = null;

    public static function getInstance(): PDO {
        if (self::$instance === null) {
            $config = require __DIR__ . '/../../config/database.php';
            $dbConfig = $config['db'];

            // Set timezone
            date_default_timezone_set($config['app']['timezone'] ?? 'Asia/Manila');

            try {
                if ($dbConfig['driver'] === 'sqlite') {
                    $sqlitePath = $dbConfig['sqlite_path'];
                    $dir = dirname($sqlitePath);
                    if (!is_dir($dir)) {
                        mkdir($dir, 0777, true);
                    }
                    self::$instance = new PDO("sqlite:" . $sqlitePath);
                    self::$instance->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
                    self::$instance->exec("PRAGMA foreign_keys = ON;");
                } else {
                    $dsn = sprintf("mysql:host=%s;port=%s;dbname=%s;charset=utf8mb4",
                        $dbConfig['host'],
                        $dbConfig['port'],
                        $dbConfig['dbname']
                    );
                    self::$instance = new PDO($dsn, $dbConfig['user'], $dbConfig['pass'], [
                        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                        PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES utf8mb4"
                    ]);
                }
            } catch (PDOException $e) {
                die("Database connection failed: " . $e->getMessage());
            }
        }
        return self::$instance;
    }

    public static function setInstance(PDO $pdo): void {
        self::$instance = $pdo;
    }
}
