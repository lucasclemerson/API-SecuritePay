<?php

require_once __DIR__ . '/../Model/Model.php';

class ModelAuth extends Model {
    protected $connection;
    protected $table = "api_user";

    public function __construct() {
        parent::__construct();
        $this->connection = $this->getConnection();
    }

    public function generateToken($userId) {
        $token = bin2hex(random_bytes(32));
        $hashedToken = hash('sha256', $token);
        $expired_token = date('Y-m-d H:i:s', strtotime('+8 hours'));
        
        $query = "UPDATE " . $this->table . " SET token = ?, expired_token = ? WHERE id = ?";
        $stmt = $this->connection->prepare($query);
        $stmt->bind_param("ssi", $hashedToken, $expired_token, $userId);
        $stmt->execute();
        return $token;
    }

    public function logout($token) {
        $query = "UPDATE " . $this->table . " SET token = NULL, expired_token = 0 WHERE token = ?";
        $stmt = $this->connection->prepare($query);
        $stmt->bind_param("s", $token);
        $result = $stmt->execute();
        return $result;
    }

    public function validateToken($token) {
        $query = "SELECT id, name, token, expired_token FROM " .$this->table. " WHERE token=?";
        $stmt = $this->connection->prepare($query);
        $stmt->bind_param("s", $token);
        $stmt->execute();
        $result = $stmt->get_result()->fetch_assoc();
        return $result;
    }

    public function login($username, $password) {
        $query = "SELECT id, name, token, expired_token FROM " . $this->table . " WHERE username = ? AND password = ?";
        $stmt = $this->connection->prepare($query);
        $password = sha1($password);
        $stmt->bind_param("ss", $username, $password);  
        $stmt->execute();
        $user = $stmt->get_result()->fetch_assoc();
        if ($user) {
            if (strtotime($user['expired_token']) < time()) {
                $newToken = $this->generateToken($user['id']);
                $user['token'] = $newToken;
                $user['expired_token'] = date('Y-m-d H:i:s', strtotime('+8 hours'));

                $this->updateTokenInDatabase($user['id'], $newToken, $user['expired_token']);
            }
            return $user;
        }
        return null;
    }

    public function updateTokenInDatabase($userId, $token, $expiredToken) {
        $query = "UPDATE " . $this->table . " SET token = ?, expired_token = ? WHERE id = ?";
        $stmt = $this->connection->prepare($query);
        $stmt->bind_param("ssi", $token, $expiredToken, $userId);
        $stmt->execute();
    }
}