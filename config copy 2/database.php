<?php
// config/database.php

require_once __DIR__ . '/config.php';

if (!class_exists('Database')) {
    class Database
    {
        private static ?PDO $instance = null;

        public static function getInstance(): PDO
        {
            if (self::$instance === null) {
                $dsn = sprintf('mysql:host=%s;dbname=%s;charset=%s', DB_HOST, DB_NAME, DB_CHARSET);
                try {
                    self::$instance = new PDO($dsn, DB_USER, DB_PASS, [
                        PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
                        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                        PDO::ATTR_EMULATE_PREPARES   => false,
                    ]);
                } catch (PDOException $e) {
                    error_log('DB Error: ' . $e->getMessage());
                    http_response_code(503);
                    die('Database unavailable. Please try again later.');
                }
            }
            return self::$instance;
        }

        private function __clone() {}
    }
}

if (!function_exists('get_db')) {
    function get_db(): PDO
    {
        return Database::getInstance();
    }
}
