<?php
require_once __DIR__.'/../config/database.php';

class Parametros {
  private $conn;
  private $table = 'parametros';

  public function __construct() {
    $database = new Database();
    $this->conn = $database->connect();
  }

  public function getAll() {
    $stmt = $this->conn->prepare("SELECT * FROM {$this->table}");

    $stmt->execute();

    return $stmt->fetchAll(PDO::FETCH_ASSOC);
  }

  public function getByChave($chave) {
    $stmt = $this->conn->prepare("SELECT * FROM {$this->table} WHERE chave = :chave LIMIT 1");

    $stmt->bindParam(':chave', $chave);

    $stmt->execute();

    return $stmt->fetch(PDO::FETCH_ASSOC);
  }
}
