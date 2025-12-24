<?php
require_once __DIR__.'/../config/database.php';

class Importacoes {
  private $conn;
  private $table = 'importacoes';

  public function __construct() {
    $database = new Database();
    $this->conn = $database->connect();
  }

  public function getAll() {
    $stmt = $this->conn->prepare("SELECT * FROM {$this->table} ORDER BY dataInclusao DESC");
    $stmt->execute();
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
  }

  public function insert($nomeArquivo) {
    $stmt = $this->conn->prepare(
      "INSERT INTO {$this->table} (nomeArquivo) VALUES (:nomeArquivo)"
    );

    $stmt->bindParam(':nomeArquivo', $nomeArquivo);

    $stmt->execute();

    return $this->conn->lastInsertId();
  }
}
