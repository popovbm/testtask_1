<?php

class Database {
    private static ?Database $instance = null; // Хранит единственный экземпляр класса
    private PDO $conn; // Подключение к базе данных

    // Приватный конструктор, чтобы предотвратить создание экземпляра извне
    private function __construct($host, $dbname, $username, $password) {
        $this->conn = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8mb4", $username, $password);
        // Установка режима обработки ошибок
        $this->conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    }

    // Метод для получения экземпляра класса
    public static function getInstance($host, $dbname, $username, $password): Database {
        if (self::$instance === null) {
            self::$instance = new Database($host, $dbname, $username, $password);
        }
        return self::$instance;
    }

    // Метод для получения подключения
    public function getConnection(): PDO {
        return $this->conn;
    }
}