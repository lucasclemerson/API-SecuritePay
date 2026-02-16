<?php

require_once __DIR__ . '/../Model/Model.php';

class ModelCardFlags extends Model {
    protected $connection;
    protected $table = "card_flags";

    public function __construct() {
        parent::__construct();
        $this->connection = $this->getConnection();
    }

    public function getAll() {
        $query = "SELECT * FROM " . $this->table . " ORDER BY created_at DESC";
        $stmt = $this->connection->prepare($query);
        $stmt->execute();   
        return $stmt->get_result()->fetch_all(MYSQLI_ASSOC);  
    }

    public function getById($id) {
        $query = "SELECT * FROM " . $this->table . " WHERE id = :id";
        $stmt = $this->connection->prepare($query);
        $stmt->bindParam(':id', $id);
        $stmt->execute();
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public function delete($id) {
        $query = "DELETE FROM " . $this->table . " WHERE id = :id";
        $stmt = $this->connection->prepare($query);
        $stmt->bindParam(':id', $id);
        return $stmt->execute();
    }
}
