<?php

require_once __DIR__ . '/../Model/ModelAuth.php';

class Auth extends ModelAuth {
    private $logFile = __DIR__ . '/logs/auth.log';
    
    public function __construct() {
        $this->ensureLogDirectory();
    }
    
    private function ensureLogDirectory() {
        $logDir = __DIR__ . '/logs';
        if (!is_dir($logDir)) {
            mkdir($logDir, 0755, true);
        }
    }
    
    public function validateToken($token) {
        $modelAuth = new \ModelAuth();
        $response = $modelAuth->validateToken($token); 
        if ($response) {
            $this->logAction('VALIDATE TOKEN', $response['name'], 'Sucesso');
            return $response;
        } 

        echo json_encode(['status' => '401', 'message' => 'Auth: Token not authorized']);
        $this->logAction('VALIDATE TOKEN', $token, 'Falha');
        exit;
    }

    public function login($username, $password) {
        $modelAuth = new \ModelAuth();
        $response = $modelAuth->login($username, $password); 
        if ($response) {
            $this->logAction('LOGIN', $username, 'Sucesso');
            return $response;
        } 

        echo json_encode(['status' => '401', 'message' => 'Auth: Login not authorized']);
        $this->logAction('LOGIN', $username, 'Falha');
        exit;
    }
    
    public function logout($token) {
        $modelAuth = new \ModelAuth();
        $response = $modelAuth->logout($token);
        if ($response) {
            $this->logAction('LOGOUT', $token, 'Sucesso');
            return true;
        }
        $this->logAction('LOGOUT', 'Desconhecido', 'Falha');
        return false;
    }
    
    private function validateLogFile() {
        if (!file_exists($this->logFile)) {
            touch($this->logFile);
        }
    }
    
    private function logAction($action, $username, $status) {
        $this->validateLogFile();
        $timestamp = date('Y-m-d H:i:s');
        $ip = $_SERVER['REMOTE_ADDR'] ?? 'Desconhecido';
        $logEntry = "[$timestamp] $action | Usuário: $username | Status: $status | IP: $ip\n";
        
        file_put_contents($this->logFile, $logEntry, FILE_APPEND);
    }
}

?>