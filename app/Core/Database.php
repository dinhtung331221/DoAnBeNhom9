<?php

namespace App\Core;

use PDO;
use PDOException;
use RuntimeException;

class Database
{
    private static $instance;
    private $pdo;

    private function __construct()
    {
        $config = require BASE_PATH . '/config/database.php';
        $dsn = sprintf('mysql:host=%s;port=%s;dbname=%s;charset=%s', $config['host'], $config['port'], $config['database'], $config['charset']);
        try {
            $this->pdo = new PDO($dsn, $config['username'], $config['password'], array(
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                PDO::ATTR_EMULATE_PREPARES => false,
            ));
        } catch (PDOException $exception) {
            error_log($exception->getMessage());
            throw new RuntimeException('Khong the ket noi co so du lieu.');
        }
    }

    public static function connection()
    {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance->pdo;
    }

    private function __clone()
    {
    }
}

