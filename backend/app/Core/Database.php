<?php

namespace App\Core;

use PDO;
use PDOException;

class Database {
    private $conn;

    public function __construct() {
        $config = require __DIR__ . '/../../config.php';

        $dsn = "mysql:host={$config['db_host']};dbname={$config['db_name']};charset=utf8mb4";

        try {
            $this->conn = new PDO($dsn, $config['db_user'], $config['db_pass'], [
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC
            ]);
        } catch (PDOException $e) {
            error_log($e->getMessage());
            die("Database connection failed.");
        }
    }

    public function getConnection() {
        return $this->conn;
    }
}


