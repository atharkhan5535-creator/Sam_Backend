<?php

class Database
{
    private static $instance = null;
    private $connection;

    private function __construct()
    {
        $host = "localhost";
        $db   = "sam-db";
        $user = "root";
        $pass = "root";

        $dsn = "mysql:host=$host;dbname=$db;charset=utf8mb4";

        $this->connection = new PDO($dsn, $user, $pass, [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC
        ]);
    }

    public static function getInstance()
    {
        if (!self::$instance) {
            self::$instance = new Database();
        }

        return self::$instance;
    }

    public function getConnection()
    {
        return $this->connection;
    }
}
