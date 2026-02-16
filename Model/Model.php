<?php

class Model {
    private $connection;
    private $envPath = '.env';

    public function __construct() {
        $this->loadEnv();
        $this->connect();
    }

    private function loadEnv() {
        $filePath = __DIR__ . '/../' . $this->envPath;
        
        if (!file_exists($filePath)) {
            throw new Exception("Model: File .env not found: $filePath");
        }

        $lines = file($filePath, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
        foreach ($lines as $line) {
            if (strpos($line, '=') !== false && strpos($line, '#') !== 0) {
                [$key, $value] = explode('=', $line, 2);
                putenv(trim($key) . '=' . trim($value));
            }
        }
    }

    private function connect() {
        $host = getenv('DB_HOST') ?: 'localhost';
        $user = getenv('DB_USER') ?: 'root';
        $password = getenv('DB_PASSWORD') ?: 'root';
        $database = getenv('DB_NAME') ?: 'your_database';

        try {
            $this->connection = new mysqli($host, $user, $password, $database);
            
            if ($this->connection->connect_error) {
                throw new Exception('Model: Connection failed: ' . $this->connection->connect_error);
            }
        } catch (Exception $e) {
            throw new Exception('Error: ' . $e->getMessage());
        }
    }

    public function getConnection() {
        return $this->connection;
    }

    public function closeConnection() {
        $this->connection->close();
    }
}
?>
