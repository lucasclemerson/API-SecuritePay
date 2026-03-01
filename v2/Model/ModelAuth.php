<?php

require_once __DIR__ . '/../Model/Model.php';

class ModelAuth {

    private $connection;
    private $table = "api_user";

    public function __construct() {
        $this->connection = Model::getConnection();
    }

    public function generateToken($userId) {

        $token = bin2hex(random_bytes(32));
        $hashedToken = hash('sha256', $token);
        $expires = date('Y-m-d H:i:s', strtotime('+8 hours'));

        $query = "UPDATE {$this->table} 
                  SET token = ?, expired_token = ? 
                  WHERE id = ?";

        $stmt = $this->connection->prepare($query);
        $stmt->bind_param("ssi", $hashedToken, $expires, $userId);
        $stmt->execute();

        return $token; 
    }

    public function logout($token) {

        $hashedToken = hash('sha256', $token);

        $query = "UPDATE {$this->table} 
                  SET token = NULL, expired_token = NULL 
                  WHERE token = ?";

        $stmt = $this->connection->prepare($query);
        $stmt->bind_param("s", $hashedToken);

        return $stmt->execute();
    }

    public function validateToken($token) {

        $hashedToken = hash('sha256', $token);

        $query = "SELECT id, name, token, expired_token 
                  FROM {$this->table} 
                  WHERE token = ? 
                  AND expired_token > NOW()";

        $stmt = $this->connection->prepare($query);
        $stmt->bind_param("s", $hashedToken);

        $stmt->execute();

        return $stmt->get_result()->fetch_assoc();
    }

    public function login($username, $password) {

        $query = "SELECT id, name, password 
                  FROM {$this->table} 
                  WHERE username = ?";

        $stmt = $this->connection->prepare($query);
        $stmt->bind_param("s", $username);
        $stmt->execute();

            $user = $stmt->get_result()->fetch_assoc();

        if (!$user) {
            return null;
        }

        // password seguro
        if (!password_verify($password, $user['password'])) {
            return null;
        }

        $token = $this->generateToken($user['id']);

        return [
            'id' => $user['id'],
            'name' => $user['name'],
            'token' => $token
            ];
    }
}