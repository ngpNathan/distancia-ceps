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

  public function updateTotalItens($importacaoId, $totalItens) {
    $stmt = $this->conn->prepare(
      "UPDATE {$this->table} SET totalItens = :totalItens WHERE id = :id"
    );

    $stmt->bindParam(':id', $importacaoId);
    $stmt->bindParam(':totalItens', $totalItens);

    $stmt->execute();
  }

  public function updateToProcessando($importacaoId) {
    $stmt = $this->conn->prepare(
      "UPDATE {$this->table} SET status = 'PROCESSANDO' WHERE id = :id"
    );

    $stmt->bindParam(':id', $importacaoId);

    $stmt->execute();
  }

  public function updateProcessados($importacaoId) {
    $stmt = $this->conn->prepare(
      "UPDATE {$this->table} SET itensProcessados = itensProcessados + 1 WHERE id = :id"
    );

    $stmt->bindParam(':id', $importacaoId);

    $stmt->execute();
  }

  public function finalizarImportacao($importacaoId) {
    $stmt = $this->conn->prepare(
      "UPDATE {$this->table} SET status = 'FINALIZADA', dataFim = NOW() WHERE id = :id"
    );

    $stmt->bindParam(':id', $importacaoId);

    $stmt->execute();
  }
}
