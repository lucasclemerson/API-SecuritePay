<?php

require_once __DIR__ . '/../Model/Model.php';

class ModelMachine {

    private $connection;
    private $table = "payment_machines";

    public function __construct() {
        $this->connection = Model::getConnection();
    }

    public function getAll() {

        $query = "SELECT * FROM {$this->table} ORDER BY ordem ASC";

        $stmt = $this->connection->prepare($query);
        $stmt->execute();

        return $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
    }

    public function getById($id) {

        $query = "SELECT * FROM {$this->table} WHERE id = ?";

        $stmt = $this->connection->prepare($query);
        $stmt->bind_param("i", $id);

        $stmt->execute();

        return $stmt->get_result()->fetch_assoc();
    }

    public function delete($id) {

        $query = "DELETE FROM {$this->table} WHERE id = ?";

        $stmt = $this->connection->prepare($query);
        $stmt->bind_param("i", $id);

        return $stmt->execute();
    }
}